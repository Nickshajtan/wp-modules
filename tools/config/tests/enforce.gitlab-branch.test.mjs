import { describe, it, expect, vi } from 'vitest';
import { enforceGitLab } from '../enforce-branches-policy.mjs';

const makeHttpMock = ({ already } = {}) => ({
  post: vi.fn(async () =>
    already ? { status: 400, data: 'already protected' } : { status: 201, data: {} }
  ),
  put: vi.fn(async () => ({ status: 200, data: {} })),
});

describe('enforceGitLab', () => {
  const config = {
    projects: [
      {
        id: 123,
        branches: [
          {
            pattern: 'main',
            push: 'maintainers',
            merge: 'maintainers',
            allow_force_push: false,
          },
        ],
      },
    ],
  };

  it('creates protection when absent', async () => {
    const http = makeHttpMock({ already: false });
    await enforceGitLab(config, { token: 'x', http });
    expect(http.post).toHaveBeenCalledWith('/projects/123/protected_branches', {
      name: 'main',
      push_access_level: 40,
      merge_access_level: 40,
      allow_force_push: false,
    });
    expect(http.put).not.toHaveBeenCalled();
  });

  it('updates when already protected', async () => {
    const http = makeHttpMock({ already: true });
    await enforceGitLab(config, { token: 'x', http });
    expect(http.put).toHaveBeenCalledWith('/projects/123/protected_branches/main', {
      push_access_level: 40,
      merge_access_level: 40,
      allow_force_push: false,
    });
  });
});
