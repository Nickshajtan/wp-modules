#!/usr/bin/env node
import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';
import axios from 'axios';
import yaml from 'js-yaml';
import kleur from 'kleur';

const GH_API = process.env.GH_API ?? 'https://api.github.com';
const GL_API = process.env.GL_API ?? 'https://gitlab.com/api/v4';
const GH_TOKEN = process.env.GH_TOKEN;
const GL_TOKEN = process.env.GL_TOKEN;
const DRY_RUN = (process.env.DRY_RUN || 'false').toLowerCase() === 'true';

const readPolicyFile = policyPath => {
  return yaml.load(fs.readFileSync(policyPath, 'utf8')) || {};
};
const enforceGitHub = async (config, { token, api, http }) => {
  if (!token || !config) {
    return;
  }

  const gh =
    http ??
    axios.create({
      baseURL: api,
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/vnd.github+json',
        'User-Agent': 'branch-protection-enforcer',
      },
      validateStatus: () => true,
    });

  for (const repo of config.repos ?? []) {
    for (const b of repo.branches ?? []) {
      const payload = {
        enforce_admins: b.enforce_admins ?? true,
        required_status_checks: b.status_checks ?? null,
        required_pull_request_reviews: b.pr_reviews ?? null,
        restrictions: null,
      };

      if (DRY_RUN) {
        continue;
      }
      const res = await gh.put(`/repos/${repo.name}/branches/${b.pattern}/protection`, payload);
      if (res.status !== 200) {
        console.warn('GitHub API', res.status, res.data);
      }
    }
  }
};
const enforceGitLab = async (config, { token, api, http }) => {
  if (!token || !config) {
    return;
  }

  const gl =
    http ??
    axios.create({
      baseURL: api,
      headers: { 'PRIVATE-TOKEN': token },
      validateStatus: () => true,
    });
  const glLevel = (name = 'maintainers') =>
    ({
      no_access: 0,
      developer: 30,
      developers: 30,
      maintainer: 40,
      maintainers: 40,
      admin: 60,
    })[String(name).toLowerCase()] ?? 40;

  for (const project of config.projects ?? []) {
    for (const b of project.branches ?? []) {
      const createPayload = {
        name: b.pattern,
        push_access_level: glLevel(b.push),
        merge_access_level: glLevel(b.merge),
        allow_force_push: !!b.allow_force_push,
      };
      if (DRY_RUN) {
        continue;
      }

      let res = await gl.post(`/projects/${project.id}/protected_branches`, createPayload);
      if (
        res.status === 400 &&
        JSON.stringify(res.data).toLowerCase().includes('already protected')
      ) {
        const encoded = encodeURIComponent(b.pattern);
        res = await gl.put(`/projects/${project.id}/protected_branches/${encoded}`, {
          push_access_level: createPayload.push_access_level,
          merge_access_level: createPayload.merge_access_level,
          allow_force_push: createPayload.allow_force_push,
        });
      }

      if (![200, 201].includes(res.status)) {
        console.warn('GitLab API', res.status, res.data);
      }
    }
  }
};
export async function main() {
  const policy = readPolicyFile(
    path.resolve(process.cwd(), '../../.repo-config/branch-policy.yml')
  );
  if (policy.github) {
    if (GH_TOKEN) {
      await enforceGitHub(policy.github, { token: process.env.GH_TOKEN, api: GH_API });
    } else {
      console.log(kleur.red(`x Enforcement needs GH_TOKEN secret ${DRY_RUN ? '(dry-run)' : ''}`));
    }
  }

  if (policy.gitlab) {
    if (GL_TOKEN) {
      await enforceGitLab(policy.gitlab, { token: process.env.GL_TOKEN, api: GL_API });
    } else {
      console.log(kleur.red(`x Enforcement needs GL_TOKEN secret ${DRY_RUN ? '(dry-run)' : ''}`));
    }
  }

  console.log(kleur.green(`✓ Enforcement completed ${DRY_RUN ? '(dry-run)' : ''}`));
}

if (import.meta.url === `file://${process.argv[1]}`) {
  main().catch(e => {
    console.error(e);
    process.exit(1);
  });
}
