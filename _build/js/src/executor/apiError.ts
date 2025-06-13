export class ApiError extends Error {
  statusCode: number;

  constructor(message: string, statusCode: number) {
    super(message);
    this.name = 'ApiError';
    this.statusCode = statusCode;

    Object.setPrototypeOf(this, ApiError.prototype);
  }
}

export const isApiError = (err: unknown): err is ApiError => {
  return err instanceof ApiError;
};
