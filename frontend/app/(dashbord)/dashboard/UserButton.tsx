"use client";

import { useSession } from "@/store/useAuthStore";

const UserButton = () => {
  const { logout } = useSession();

  return (
    <div>
      <button
        onClick={logout}
        className="bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-4 rounded mt-3 cursor-pointer"
      >
        Logout
      </button>
    </div>
  );
};

export default UserButton;
