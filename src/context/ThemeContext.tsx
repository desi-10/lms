import { ReactNode, createContext, useContext, useState } from "react";

type Theme = "light" | "dark";

interface IThemeContext {
  theme: Theme;
  toggleTheme: () => void;
}

const ThemeContext = createContext<IThemeContext | null>(null);

export const ThemeProvider = ({ children }: { children: ReactNode }) => {
  const [theme, setTheme] = useState<Theme>("light");

  const toggleTheme = () => {
    const newTheme: Theme = theme === "light" ? "dark" : "light";
    setTheme(newTheme);
  };

  return (
    <ThemeContext.Provider value={{ theme, toggleTheme }}>
      {children}
    </ThemeContext.Provider>
  );
};

export const useTheme = () => {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error("useTheme must be used within a ThemeProvider");
  }
  return context;
};
