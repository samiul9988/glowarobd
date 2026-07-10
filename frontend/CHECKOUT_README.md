# Optimized Checkout System

This is a complete rewrite of your checkout system using modern React patterns, React Query for state management, and clean separation of concerns.

## Key Improvements

### 1. **Clean Architecture**
- **API Layer**: Centralized API calls in `lib/api/checkout.ts`
- **React Query Hooks**: Custom hooks for data fetching and mutations in `hooks/queries/useCheckout.ts`
- **Component Separation**: Each part of checkout is a separate, focused component

### 2. **Better State Management**
- Uses React Query for server state management
- Automatic caching and background refetching
- Optimistic updates for better UX
- Proper loading and error states

### 3. **Improved User Experience**
- Real-time address validation
- Auto-selection of first address
- Proper loading states throughout
- Better error handling with toast notifications
- Responsive design with proper mobile support

### 4. **Performance Optimizations**
- Data is cached and only refetched when necessary
- Parallel API calls where possible
- Optimistic updates for immediate feedback
- Proper dependency management in useEffect

## File Structure

```
lib/
  ├── api/
  │   └── checkout.ts          # All API calls and types
  ├── http-client.ts           # HTTP client with token handling
  └── auth-utils.ts            # Authentication utilities

hooks/queries/
  └── useCheckout.ts           # React Query hooks

components/checkout/
  ├── OptimizedCheckout.tsx    # Main checkout component
  ├── AddressSelector.tsx      # Address selection component
  ├── AddressForm.tsx          # Add/edit address form
  ├── PaymentMethods.tsx       # Payment method selection
  └── OrderSummary.tsx         # Order summary sidebar

components/ui/                 # Reusable UI components
providers/
  └── QueryProvider.tsx        # React Query provider setup
```

## Installation

### 1. Install Required Dependencies

If you don't have these packages, install them:

```bash
npm install zustand @hookform/resolvers react-hook-form zod

# Optional: For better separator component (we have a fallback)
npm install @radix-ui/react-separator

# Optional: For React Query DevTools (development only)
npm install --save-dev @tanstack/react-query-devtools
```

**Note**: The system will work without `@radix-ui/react-separator` as we provide a fallback separator component.

## Usage

### 1. Setup React Query Provider

Wrap your app with the QueryProvider:

```tsx
// app/layout.tsx or _app.tsx
import QueryProvider from '@/providers/QueryProvider';

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en">
      <body>
        <QueryProvider>
          {children}
        </QueryProvider>
      </body>
    </html>
  );
}
```

### 2. Use the Optimized Checkout

```tsx
// app/checkout/page.tsx
import OptimizedCheckout from '@/components/checkout/OptimizedCheckout';

export default function CheckoutPage() {
  return <OptimizedCheckout />;
}
```

### 3. Environment Variables

Make sure you have these environment variables set:

```env
NEXT_PUBLIC_API_BASE_URL=your_api_base_url
NEXT_PUBLIC_IMAGE_BASE_URL=your_image_base_url
```

## API Integration

The system uses the following APIs you provided:

- `POST /shipping_cost` - Calculate shipping cost
- `GET /user/shipping/address/{user_id}` - Get user addresses
- `POST /user/shipping/create` - Create new address
- `POST /user/shipping/update` - Update existing address
- `DELETE /user/shipping/delete/{id}` - Delete address
- `GET /cities-by-state/{state_id}` - Get cities by state
- `GET /states-by-country/{country_id}` - Get states by country
- `GET /countries` - Get all countries
- `GET /areas-by-city/{city_id}` - Get areas by city
- `POST /update-address-in-cart` - Update cart with selected address
- `GET /payment-types` - Get available payment methods
- `POST /order/store` - Create order
- `POST /payments/pay/cod` - Process COD payment
- `POST /cartswithdelivery/{user_id}/{address_id}` - Get cart with delivery info

## Authentication & Security

### Token Management
- **Automatic Token Handling**: All API calls automatically include authentication tokens
- **Client-Side**: Tokens stored in localStorage and cookies for persistence
- **Server-Side**: Tokens retrieved from cookies for SSR compatibility
- **Auto-Logout**: Automatically redirects to login on 401 errors
- **Token Refresh**: Handles token expiration gracefully

### Security Features
- Bearer token authentication on all requests
- Automatic token cleanup on logout
- Secure cookie storage with SameSite protection
- Request/response interceptors for error handling

## Key Features

### Address Management
- View all saved addresses
- Add new addresses with proper validation
- Edit existing addresses
- Delete addresses with confirmation
- Auto-select first address for convenience

### Location Selection
- Cascading dropdowns for State → City → Area
- Proper loading states for each dropdown
- Data caching to avoid repeated API calls

### Payment Methods
- Dynamic payment method loading from API
- Visual icons for different payment types
- Support for COD and online payments

### Order Processing
- Proper validation before order placement
- Loading states during order processing
- Error handling with user-friendly messages
- Automatic cart clearing on success
- Order tracking setup

## Benefits Over Original Code

1. **Maintainability**: Clean separation of concerns, easier to debug and extend
2. **Performance**: Better caching, fewer unnecessary re-renders
3. **User Experience**: Better loading states, error handling, and feedback
4. **Type Safety**: Full TypeScript support with proper types
5. **Testability**: Each component and hook can be tested independently
6. **Scalability**: Easy to add new features or modify existing ones

## Migration from Old Code

To migrate from your existing checkout:

1. Install the new components
2. Replace your existing checkout page with `OptimizedCheckout`
3. Update your API base URLs in environment variables
4. Test the flow thoroughly
5. Remove old checkout components once verified

The new system maintains the same user flow but with much better code organization and user experience.