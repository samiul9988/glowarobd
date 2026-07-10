import React from "react";
import AuthContainer from "../_components/AuthContainer";
import ForgotPasswordForm from "@/components/modals/_components/ForgotPasswordForm";

export default function page() {
  return (
    <div>
      <AuthContainer>
        <ForgotPasswordForm />
      </AuthContainer>
    </div>
  );
}
