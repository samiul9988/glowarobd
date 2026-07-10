export async function convertToBase64(
  file: File
): Promise<{ base64: string; fileName: string }> {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();

    reader.onload = () => {
      const base64 = reader.result as string;
      resolve({ base64, fileName: file.name });
    };

    reader.onerror = (error) => reject(error);

    reader.readAsDataURL(file);
  });
}
