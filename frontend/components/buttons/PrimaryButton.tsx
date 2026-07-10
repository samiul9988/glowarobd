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

export default function PrimaryButton({
  link,
  children,
  className,
  ...props
}: PrimaryButtonProps) {
  const baseStyles = clsx(
    "block w-full cursor-pointer rounded-full bg-site-secondary-500 py-2.5 md:py-3.5 text-center text-white transition-colors hover:bg-site-secondary-600 font-bold text-base",
    className
  );

  if (link) {
    return (
      <Link href={link} className={baseStyles} {...(props as LinkProps)} >
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
