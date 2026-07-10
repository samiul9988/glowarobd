import clsx from "clsx";
import React from "react";

interface Props extends React.ComponentProps<"div"> {
  children: React.ReactNode;
}

const AuthContainer = ({ children, className }: Props) => {
  return (
    <div className="bg-site-secondary-50 py-10 md:py-20">
      <div className={clsx("mx-auto max-w-[450px] px-2", className)}>
        <div className="border-site-gray-100 rounded-2xl border bg-white">
          {children}
        </div>
      </div>
    </div>
  );
};

export default AuthContainer;
