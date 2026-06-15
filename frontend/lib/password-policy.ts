/**
 * Client-side mirror of backend PasswordPolicy (demo: exactly 6 digits).
 * Replace pattern/length when swapping to a production policy.
 */
export const PASSWORD_POLICY = {
  length: 6,
  pattern: /^\d{6}$/,
  isValid(password: string): boolean {
    return this.pattern.test(password)
  },
} as const
