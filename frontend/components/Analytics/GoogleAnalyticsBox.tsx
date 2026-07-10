import { getBusinessSettings } from "@/actions/getBusinessSettings";
import GoogleAnalyticsClient from "./GoogleAnalyticsClient";

/**
 * Google analytics + tag manager injection
 */
export default async function GoogleAnalyticsBox() {
  const businessSettings = (await getBusinessSettings()) as BusinessDataType[];

  // Collect settings in a single pass
  const settings = businessSettings?.reduce(
    (acc, item) => {
      switch (item.type) {
        case "google_analytics":
          acc.googleAnalytics = item.value;
          break;
        case "google_tagmanager":
          acc.googleTagManager = item.value;
          break;
        case "google_analytics_id":
          acc.googleAnalyticsId = item.value;
          break;
        case "google_tagmanager_id":
          acc.googleTagManagerId = item.value;
          break;
      }
      return acc;
    },
    {
      googleAnalytics: null as string | null,
      googleTagManager: null as string | null,
      googleAnalyticsId: null as string | null,
      googleTagManagerId: null as string | null,
    },
  );

  const isEnableGoogleAnalytics = settings?.googleAnalytics == "1";
  const isEnableGoogleTagManager = settings?.googleTagManager == "1";
  const googleAnalyticsId = settings?.googleAnalyticsId || "";
  const googleTagManagerId = settings?.googleTagManagerId || "";

  return (
    <>
      <GoogleAnalyticsClient
        isEnableGoogleAnalytics={isEnableGoogleAnalytics}
        googleAnalyticsId={googleAnalyticsId}
        isEnableGoogleTagManager={isEnableGoogleTagManager}
        googleTagManagerId={googleTagManagerId}
      />
    </>
  );
}
