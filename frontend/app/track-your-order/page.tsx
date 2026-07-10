import PageHeader from "@/components/PageHeader";
import { ChevronRight } from "lucide-react";
import TrackOrderSection from "./_sections/TrackOrderSection";

const TrackYourOrder = () => {
  return (
    <section>
      <PageHeader
        title="Track Your Order"
        breadcrumb={
          <span className="flex items-center gap-2 text-sm font-medium text-white/50">
            Home <ChevronRight size={15} />
            <span className="text-white/80">Track Order</span>
          </span>
        }
        animateImg1Url="/images/cart-icon-sm.png"
        animateImg1Style="h-[70px] w-[70px] md:h-[120px] md:w-[120px] lg:h-[158px] lg:w-[158px] absolute left-2 md:left-12 -top-5"
        animateImg2Url="/images/cart-icon.png"
        animateImg2Style="h-[80px] w-[80px] md:h-[120px] md:w-[120px] lg:h-[170px] lg:w-[170px] absolute -right-2 bottom-4 md:-bottom-1"
      />

      {/* Track Order */}
      <TrackOrderSection />
    </section>
  );
};

export default TrackYourOrder;
