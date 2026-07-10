export const generateGuestId = (): string => {
  const timestamp = Date.now().toString().slice(-8);

  const letters = Array.from({ length: 3 }, () => {
    const chars = "abcdefghijklmnopqrstuvwxyz";
    return chars.charAt(Math.floor(Math.random() * chars.length));
  }).join("");

  const randomNumbers = Math.floor(Math.random() * 9000 + 1000);

  const extraLetter = "abcdefghijklmnopqrstuvwxyz".charAt(
    Math.floor(Math.random() * 26)
  );

  return "tmp" + timestamp + letters + randomNumbers + extraLetter;
};
