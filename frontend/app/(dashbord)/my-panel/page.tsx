import Container from "@/components/Container";
import MobileBottomNavigationSheet from "@/components/layout/_components/MobileBottomNavigationSheet";
import { metaData } from "@/metadata/staticMetaData";

export default function myPanel() {
  return (
    <>
      <Container>
        <MobileBottomNavigationSheet />
      </Container>
    </>
  );
}

export const metadata = {
  title: metaData.user.title,
  description: metaData.user.description,
};
