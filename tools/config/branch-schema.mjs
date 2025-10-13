import { z } from 'zod';

export const PolicySchema = z.object({
  github: z
    .object({
      repos: z.array(
        z.object({
          name: z.string(),
          branches: z.array(
            z.object({
              pattern: z.string(),
              enforce_admins: z.boolean().optional(),
              pr_reviews: z.any().optional(),
              status_checks: z.any().optional(),
            })
          ),
        })
      ),
    })
    .optional(),
  gitlab: z
    .object({
      projects: z.array(
        z.object({
          id: z.union([z.string(), z.number()]),
          branches: z.array(
            z.object({
              pattern: z.string(),
              push: z.string().optional(),
              merge: z.string().optional(),
              allow_force_push: z.boolean().optional(),
            })
          ),
        })
      ),
    })
    .optional(),
});
