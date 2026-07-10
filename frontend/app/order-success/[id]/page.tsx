import { fetcher } from "@/lib/fetcher";
import OrderSuccessDetails from "../_section/OrderDetails";
import { decryptId } from "@/lib/encryption";

export default async function OrderSuccess({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const decryptedId = decryptId(id);
  const orderDetails = await fetcher(`/purchase-history-details/${decryptedId}`);
  const orderProduct = await fetcher(`/purchase-history-items/${decryptedId}`);
  return (
    <>
      {orderDetails && (
        <OrderSuccessDetails
          orderDetails={orderDetails}
          orderProduct={orderProduct}
          orderId={decryptedId}
        />
      )}
    </>
  );
}
