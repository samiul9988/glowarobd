import { FlashDealZoneTab } from "./FlashDealZoneTab";

interface Props {
  productsByFlashDeal: {
    flashDeal: FlashDealType;
    products: ProductType[] | undefined;
  }[];
}

const FlashDealZone = ({ productsByFlashDeal }: Props) => {
  return <FlashDealZoneTab data={productsByFlashDeal} />;
};

export default FlashDealZone;
