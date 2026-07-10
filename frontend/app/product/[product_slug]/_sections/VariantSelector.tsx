"use client";
import { useEffect, useState } from "react";

interface Size {
  value: string;
  label: string;
  available?: boolean;
}

interface ProductChoiceItem {
  name: string;
  title: string;
  options: string[];
}
interface ColorOptions {
  [hexCode: string]: string;
}

interface VariantSelectProps {
  colorOptions: ColorOptions;
  choiceOption: ProductChoiceItem[];
  onVariantChange?: (variant: Record<string, string>) => void;
  className?: string;
  title?: string;
}

const VariantSelector = ({
  colorOptions,
  choiceOption,
  onVariantChange,
  className = "",
}: VariantSelectProps) => {
  const allColor = Object.keys(colorOptions); //convert array
  const [selectedColor, setSelectedColor] = useState<string>(allColor[0] || "");
  const [selectedOptions, setSelectedOptions] = useState<
    Record<string, string>
  >({});

  // initialize first variant option as default
  useEffect(() => {
    if (choiceOption?.length) {
      const defaults: Record<string, string> = {};
      choiceOption.forEach((item, idx) => {
        if (item.options.length > 0) {
          // select first option of each variant
          defaults[item.name] = item.options[0];
        }
      });
      setSelectedOptions(defaults);
      onVariantChange?.({ color: colorOptions[selectedColor], ...defaults });
    }
  }, [choiceOption, colorOptions]);

  const handleOptionChange = (name: string, value: string) => {
    setSelectedOptions((prev) => {
      const updated = { ...prev, [name]: value };
      onVariantChange?.({ color: colorOptions[selectedColor], ...updated });
      return updated;
    });
  };

  return (
    allColor?.length > 0 ||
    (choiceOption?.length > 0 && (
      <div className="rounded-2.5 mb-2 flex flex-col gap-4 border border-gray-50 p-1 md:p-4">
        {/* Color selector */}
        {allColor.length > 0 && (
          <div className="mb-2">
            <p className="mb-2 text-sm font-medium tracking-wide text-gray-400 uppercase">
              Color:
            </p>
            <div className="flex flex-wrap gap-2 md:gap-3">
              {allColor.map((color, idx) => (
                <label key={idx} className="relative inline cursor-pointer">
                  <input
                    type="radio"
                    name="color"
                    value={color}
                    checked={selectedColor === color}
                    onChange={() => {
                      setSelectedColor(color);
                      onVariantChange?.({
                        color: colorOptions[color],
                        ...selectedOptions,
                      });
                    }}
                    className="sr-only"
                  />
                  <div
                    className={`h-6 w-6 rounded-full border-2 ${
                      selectedColor === color ? "ring-2 ring-blue-500" : ""
                    }`}
                    style={{ backgroundColor: color }}
                    title={colorOptions[color]}
                  />
                </label>
              ))}
            </div>
          </div>
        )}

        {/* Other options */}
        {choiceOption.map((item, inx) => (
          <div className={`mb-2 ${className}`} key={inx}>
            <p className="mb-2 text-sm font-medium tracking-wide text-gray-400 uppercase">
              {item.title}:
            </p>
            <div className="flex flex-wrap gap-3 md:gap-4">
              {item.options.map((option, index) => (
                <label key={index} className="cursor-pointer">
                  <input
                    type="radio"
                    name={item.name}
                    value={option}
                    checked={selectedOptions[item.name] === option}
                    onChange={() => handleOptionChange(item.name, option)}
                    className="sr-only"
                  />
                  <div
                    className={`flex items-center gap-2 rounded-full border-2 p-2 ${
                      selectedOptions[item.name] === option
                        ? "border-blue-500 text-blue-700"
                        : "border-gray-50 text-gray-700"
                    }`}
                  >
                    <div
                      className={`flex h-5 w-5 items-center justify-center rounded-full ${
                        selectedOptions[item.name] === option
                          ? "border-blue-500 bg-blue-500"
                          : "border border-gray-300 text-gray-700"
                      }`}
                    >
                      <div className="h-2 w-2 rounded-full bg-white"></div>
                    </div>
                    {option}
                  </div>
                </label>
              ))}
            </div>
          </div>
        ))}
      </div>
    ))
  );
};

export default VariantSelector;
