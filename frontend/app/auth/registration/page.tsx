import React from "react";
import AuthContainer from "../_components/AuthContainer";
import SignUpForm from "@/components/modals/_components/SignUpForm";

export default function page() {
  return (
    <AuthContainer>
      <SignUpForm />
    </AuthContainer>
  );
}
