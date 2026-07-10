"use client";

import { useState } from "react";
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";
import { Label } from "@/components/ui/label";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";

export default function FilterAccordion() {
  const [selected, setSelected] = useState("");

  const filters = [
    {
      id: "cat1",
      title: "Electronics",
      children: [
        {
          id: "mobiles",
          title: "Mobiles",
          children: [
            { id: "android", title: "Android" },
            { id: "ios", title: "iOS" },
          ],
        },
        {
          id: "laptops",
          title: "Laptops",
          children: [
            { id: "gaming", title: "Gaming" },
            { id: "ultrabook", title: "Ultrabook" },
          ],
        },
      ],
    },
    {
      id: "cat2",
      title: "Fashion",
      children: [
        {
          id: "men",
          title: "Men",
          children: [
            { id: "shirts", title: "Shirts" },
            { id: "jeans", title: "Jeans" },
          ],
        },
        {
          id: "women",
          title: "Women",
          children: [
            { id: "dresses", title: "Dresses" },
            { id: "saree", title: "Saree" },
          ],
        },
      ],
    },
  ];

  const renderChildren = (items: any[]) => (
    <Accordion type="single" collapsible className="pl-4">
      {items.map((item) =>
        item.children ? (
          <AccordionItem key={item.id} value={item.id}>
            <AccordionTrigger>{item.title}</AccordionTrigger>
            <AccordionContent>{renderChildren(item.children)}</AccordionContent>
          </AccordionItem>
        ) : (
          <div key={item.id} className="flex items-center space-x-2 py-1">
            <RadioGroup
              value={selected}
              onValueChange={setSelected}
              className="flex flex-col space-y-1"
            >
              <div className="flex items-center space-x-2">
                <RadioGroupItem value={item.id} id={item.id} />
                <Label htmlFor={item.id}>{item.title}</Label>
              </div>
            </RadioGroup>
          </div>
        )
      )}
    </Accordion>
  );

  return (
    <Accordion type="single" collapsible className="w-full">
      {filters.map((filter) => (
        <AccordionItem key={filter.id} value={filter.id}>
          <AccordionTrigger>{filter.title}</AccordionTrigger>
          <AccordionContent>{renderChildren(filter.children)}</AccordionContent>
        </AccordionItem>
      ))}
      <div className="mt-4 p-2 text-sm text-gray-600">
        ✅ Selected: <span className="font-medium">{selected || "None"}</span>
      </div>
    </Accordion>
  );
}
