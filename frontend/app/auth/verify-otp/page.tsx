import React from "react";
import AuthContainer from "../_components/AuthContainer";
import VerifyOTPForm from "@/components/modals/_components/VerifyOTPForm";

export default function page() {
  return (
    <AuthContainer>
      <VerifyOTPForm />
    </AuthContainer>
  );
}
