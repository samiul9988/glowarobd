"use client";

import clsx from "clsx";
import Link from "next/link";
import { ButtonHTMLAttributes, AnchorHTMLAttributes, ReactNode } from "react";

interface PrimaryButtonBaseProps {
  children: ReactNode;
  className?: string;
}

type ButtonProps = PrimaryButtonBaseProps &
  ButtonHTMLAttributes<HTMLButtonElement> & {
    link?: undefined;
  };

type LinkProps = PrimaryButtonBaseProps &
  AnchorHTMLAttributes<HTMLAnchorElement> & {
    link: string;
  };

type PrimaryButtonProps = ButtonProps | LinkProps;

export default function SecondaryButton({
  link,
  children,
  className,
  ...props
}: PrimaryButtonProps) {
  const baseStyles = clsx(
    "flex items-center justify-center gap-2 w-full cursor-pointer rounded-full bg-site-gray-50 py-2.5 md:py-3.5 text-center text-site-gray-800 transition-colors hover:bg-site-gray-100 font-bold text-base border border-site-gray-100",
    className,
  );

  if (link) {
    return (
      <Link href={link} className={baseStyles} {...(props as LinkProps)}>
        {children}
      </Link>
    );
  }

  return (
    <button className={baseStyles} {...(props as ButtonProps)}>
      {children}
    </button>
  );
}
