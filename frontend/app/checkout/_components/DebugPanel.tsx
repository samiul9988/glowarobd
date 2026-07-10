'use client';

import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';

interface DebugPanelProps {
  data: {
    addresses?: any;
    cartWithDeliveryData?: any;
    paymentMethods?: any;
    cartProducts?: any;
    userId?: number;
    canPlaceOrder?: boolean;
    selectedAddressId?: number | null;
    selectedDeliveryMethod?: string;
    selectedPaymentMethod?: string;
  };
}

export default function DebugPanel({ data }: DebugPanelProps) {
  const [isOpen, setIsOpen] = useState(false);

  if (process.env.NODE_ENV === 'production') {
    return null;
  }

  return (
    <Card className="mt-4 border-orange-200 bg-orange-50">
      <CardHeader>
        <CardTitle className="text-sm flex justify-between items-center">
          Debug Panel
          <Button
            variant="outline"
            size="sm"
            onClick={() => setIsOpen(!isOpen)}
          >
            {isOpen ? 'Hide' : 'Show'}
          </Button>
        </CardTitle>
      </CardHeader>
      {isOpen && (
        <CardContent>
          <div className="space-y-4 text-xs">
            <div>
              <strong>Selected Address ID:</strong> {data.selectedAddressId || 'None'}
            </div>
            <div>
              <strong>Selected Delivery Method:</strong> {data.selectedDeliveryMethod || 'None'}
            </div>
            <div>
              <strong>Selected Payment Method:</strong> {data.selectedPaymentMethod || 'None'}
            </div>
            <div>
              <strong>Addresses Count:</strong> {data.addresses?.length || 0}
            </div>
            <div>
              <strong>Cart Data Available:</strong> {data.cartWithDeliveryData ? 'Yes' : 'No'}
            </div>
            <div>
              <strong>Cart Products Count:</strong> {data.cartProducts?.length || 0}
            </div>
            <div>
              <strong>User ID:</strong> {data.userId || 'None'}
            </div>
            <div>
              <strong>Can Place Order:</strong> {data.canPlaceOrder ? 'Yes' : 'No'}
            </div>
            {data.cartWithDeliveryData && (
              <div>
                <strong>Shipping Methods:</strong> {data.cartWithDeliveryData[0]?.shipping_type?.[0]?.methods?.length || 0}
              </div>
            )}
            <div>
              <strong>Payment Methods Count:</strong> {Array.isArray(data.paymentMethods) ? data.paymentMethods.length : (data.paymentMethods?.data?.length || 0)}
            </div>
            <details className="mt-4">
              <summary className="cursor-pointer font-semibold">Raw Data</summary>
              <pre className="mt-2 p-2 bg-gray-100 rounded text-xs overflow-auto max-h-40">
                {JSON.stringify(data, null, 2)}
              </pre>
            </details>
          </div>
        </CardContent>
      )}
    </Card>
  );
}