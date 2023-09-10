import { Routes, Route } from "react-router-dom";
import RootLayout from "./layouts/RootLayout";
import Home from "./pages/Home";
import Login from "./pages/Login";
import Register from "./pages/Register";
function App() {
  return (
    <>
      <Routes>
        <Route index element={<Login />} />
        <Route path="/register" element={<Register />} />
        <Route path="/dashboard" element={<RootLayout />}>
          <Route index element={<Home />} />
        </Route>
      </Routes>
    </>
  );
}

export default App;
