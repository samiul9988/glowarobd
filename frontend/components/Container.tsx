import clsx from "clsx";
import React from "react";

interface Props extends React.ComponentProps<"div"> {
  children: React.ReactNode;
}

const Container = ({ children, className }: Props) => {
  return (
    <div
      className={clsx(
        "xl:max-w-[1280px] 2xl:max-w-[1400px] mx-auto px-2 md:px-5",
        className
      )}
    >
      {children}
    </div>
  );
};

export default Container;
