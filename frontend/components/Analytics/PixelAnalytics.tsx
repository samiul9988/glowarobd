import { getBusinessSettings } from "@/actions/getBusinessSettings";
import { pixelAnalyticsId } from "@/config/apiConfig";
import Script from "next/script";

interface BusinessDataType {
  type: string;
  value: string;
}

const PixelAnalyticsBox = async () => {
  const businessSettings = (await getBusinessSettings()) as BusinessDataType[];

  const isPixelSettingOn = businessSettings.find(
    (item) => item.type === "facebook_pixel",
  )?.value;

  if (isPixelSettingOn !== "1" || !pixelAnalyticsId) return null;

  return (
    <>
      <Script
        id="fb-pixel"
        strategy="afterInteractive"
        dangerouslySetInnerHTML={{
          __html: `
            !function(f,b,e,v,n,t,s){
              if(f.fbq)return;n=f.fbq=function(){
                n.callMethod ? n.callMethod.apply(n,arguments) : n.queue.push(arguments)
              };
              if(!f._fbq)f._fbq=n;
              n.push=n;
              n.loaded=!0;
              n.version='2.0';
              n.queue=[];
              t=b.createElement(e);
              t.async=!0;
              t.src=v;
              s=b.getElementsByTagName(e)[0];
              s.parentNode.insertBefore(t,s);
            }(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '${pixelAnalyticsId}');
            fbq('track', 'PageView');
          `,
        }}
      />
      <noscript>
        <img
          height="1"
          width="1"
          style={{ display: "none" }}
          src={`https://www.facebook.com/tr?id=${pixelAnalyticsId}&ev=PageView&noscript=1`}
          alt=""
        />
      </noscript>
    </>
  );
};

export default PixelAnalyticsBox;
