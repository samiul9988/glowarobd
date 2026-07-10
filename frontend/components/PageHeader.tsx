"use client";

import { motion } from "framer-motion";
import Image from "next/image";
import Container from "./Container";

interface Props {
  title?: string;
  breadcrumb?: React.ReactNode;
  className?: string;
  pageBanner?: string;
  animateImg1Url?: string;
  animateImg2Url?: string;
  animateImg4Url?: string;
  animateImg1Style?: string;
  animateImg2Style?: string;
  animateImg4Style?: string;
  headingStyle?: string;
}

const PageHeader = ({
  title,
  className,
  pageBanner,
  breadcrumb,
  animateImg1Url,
  animateImg2Url,
  animateImg4Url,
  animateImg1Style,
  animateImg2Style,
  animateImg4Style,
  headingStyle = "text-white",
}: Props) => {
  return (
    <section className="mt-5 md:mt-10">
      <Container>
        <div
          className={`relative overflow-hidden rounded-[10px] bg-cover bg-no-repeat ${className}`}
          style={{
            backgroundImage: pageBanner ? `url(${pageBanner})` : undefined,
          }}
        >
          <div className="flex flex-col items-center justify-center gap-1 md:gap-2">
            {breadcrumb && breadcrumb}
            {title && (
              <h2
                className={`text-2xl leading-12 font-bold md:text-[50px] md:leading-14 ${headingStyle}`}
              >
                {title}
              </h2>
            )}
          </div>

          {/* Animate images 1 */}
          {animateImg1Url && (
            <motion.div
              animate={{
                x: [0, 15, -10, 20, -15, 0],
                y: [0, -20, 15, -10, 20, 0],
                rotate: [0, 5, -5, 10, 10, 0],
                scale: [1, 1.05, 0.95, 1.02, 1],
              }}
              transition={{
                duration: 20,
                repeat: Infinity,
                ease: "easeInOut",
              }}
              className={animateImg1Style}
            >
              <Image
                src={animateImg1Url}
                alt="animate-img-1"
                fill
                style={{ objectFit: "contain" }}
              />
            </motion.div>
          )}

          {/* Animate images 3 */}
          {animateImg2Url && (
            <motion.div
              animate={{
                x: [0, 15, -10, 20, -15, 0],
                y: [0, -20, 15, -10, 20, 0],
                rotate: [0, 5, -5, 10, -10, 0],
                scale: [1, 1.05, 0.95, 1.02, 1],
              }}
              transition={{
                duration: 25,
                repeat: Infinity,
                ease: "easeInOut",
              }}
              className={animateImg2Style}
            >
              <Image
                src={animateImg2Url}
                alt="animate-img-2"
                fill
                style={{ objectFit: "contain" }}
              />
            </motion.div>
          )}

          {/* Animate images 3 */}
          {/* {animateImg3Url && (
            <motion.div
              animate={{
                x: [0, 15, -10, 20, -15, 0],
                y: [0, 20, 15, -10, 20, 0],
                rotate: [0, 5, -5, 10, -10, 0],
                scale: [1, 1.05, 0.95, 1.02, 1],
              }}
              transition={{
                duration: 20,
                repeat: Infinity,
                ease: "easeInOut",
              }}
              className={animateImg3Style}
            >
              <Image
                src={animateImg3Url}
                alt="animate-img-3"
                fill
                style={{ objectFit: "contain" }}
              />
            </motion.div>
          )} */}

          {/* Animate images 4 */}
          {animateImg4Url && (
            <motion.div
              animate={{
                x: [0, 15, -10, 20, -15, 0],
                y: [0, -20, 15, -10, 20, 0],
                rotate: [0, 5, -5, 10, -10, 0],
                scale: [1, 1.05, 0.95, 1.02, 1],
              }}
              transition={{
                duration: 20,
                repeat: Infinity,
                ease: "easeInOut",
              }}
              className={animateImg4Style}
            >
              <Image
                src={animateImg4Url}
                alt="animate-img-4"
                fill
                style={{ objectFit: "contain" }}
              />
            </motion.div>
          )}
        </div>
      </Container>
    </section>
  );
};

export default PageHeader;
