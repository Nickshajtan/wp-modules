import { describe, it, expect, vi, beforeEach } from 'vitest';
import { enforceGitHub } from '../enforce-branches-policy.mjs';

const makeHttpMock = () => ({ put: vi.fn(async () => ({ status: 200, data: {} })) });
describe('enforceGitHub', () => {
  let http;
  const config = {
    repos: [
      {
        name: 'org/repo',
        branches: [
          {
            pattern: 'main',
            enforce_admins: true,
            pr_reviews: { required_approving_review_count: 1 },
            status_checks: { strict: true, contexts: ['ci/test'] },
          },
        ],
      },
    ],
  };

  beforeEach(() => {
    http = makeHttpMock();
  });

  it('calls GitHub protection endpoint with payload', async () => {
    await enforceGitHub(config, { token: 't', http });
    expect(http.put).toHaveBeenCalledWith('/repos/org/repo/branches/main/protection', {
      enforce_admins: true,
      required_status_checks: { strict: true, contexts: ['ci/test'] },
      required_pull_request_reviews: { required_approving_review_count: 1 },
      restrictions: null,
    });
  });
});
