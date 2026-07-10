"use client";

import {  useEffect, useState } from "react";
import { getBusinessSettings } from "@/actions/getBusinessSettings";

interface Props {
    key: string;
    arrayIndex: number;
}

export function useLoadBusinessSettings({key, arrayIndex = 0} : Props) {
   const [settings, setSettings] = useState<BusinessDataType[]>();


  useEffect(() => {
    (async () => {
      const data = await getBusinessSettings();
      setSettings(data);
    })();
  }, []);

return settings?.filter(setting => setting.type === key)[arrayIndex];

}
