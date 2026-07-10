import { httpClient } from './http-client';

/**
 * Set authentication token for API calls
 * This should be called after successful login
 */
export const setAuthToken = (token: string) => {
  httpClient.setToken(token);
};

/**
 * Clear authentication token
 * This should be called on logout
 */
export const clearAuthToken = () => {
  if (typeof window !== 'undefined') {
    localStorage.removeItem('access_token');
    document.cookie = 'access_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
  }
};

/**
 * Get current token from storage
 */
export const getAuthToken = (): string | null => {
  if (typeof window === 'undefined') {
    return null;
  }
  
  return localStorage.getItem('access_token') || 
         document.cookie
           .split('; ')
           .find(row => row.startsWith('access_token='))
           ?.split('=')[1] || null;
};