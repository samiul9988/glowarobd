"use client";

import { Sheet, SheetContent, SheetTrigger } from "@/components/ui/sheet";
import { useCartStore } from "@/store/useCartStore";
import CartCountWrapper from "./CartCountWrapper";
import CartDrawer from "./CartDrawer";

const Cart = () => {
  const { isOpen, setOpen } = useCartStore();

  return (
    <Sheet open={isOpen} onOpenChange={setOpen}>
      <SheetTrigger asChild>
        <div className="relative cursor-pointer text-site-gray-900 hover:bg-site-gray-100/60 rounded-full p-1 md:p-1.5 transition-all">
          <CartCountWrapper />
        </div>
      </SheetTrigger>

      <SheetContent
        side="right"
        className="!w-[85%] sm:!max-w-[400px] md:!max-w-[456px] [&>button]:hidden flex flex-row gap-0 justify-end border-0 shadow-none bg-white"
      >
        {/* Cart Drawer */}
        <CartDrawer />
      </SheetContent>
    </Sheet>
  );
};

export default Cart;
