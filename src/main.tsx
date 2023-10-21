import React from "react";
import ReactDOM from "react-dom/client";
import App from "./App.tsx";
import "./index.css";
import { BrowserRouter } from "react-router-dom";
import StudentContext from "./context/StudentContext.tsx";

ReactDOM.createRoot(document.getElementById("root")!).render(
  <React.StrictMode>
    <BrowserRouter>
      <StudentContext>
        <App />
      </StudentContext>
    </BrowserRouter>
  </React.StrictMode>
);
