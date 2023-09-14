import { Routes, Route } from "react-router-dom";
import RootLayout from "./layouts/RootLayout";
import Home from "./pages/Home";
import Login from "./pages/Login";
import RestrictedRoute from "./layouts/RestrictedRoute";
import ProtectedRoute from "./layouts/ProtectedRoute";
function App() {
  return (
    <>
      <Routes>
        <Route element={<RestrictedRoute />}>
          <Route index element={<Login />} />
        </Route>
        <Route element={<ProtectedRoute />}>
          <Route path="/dashboard" element={<RootLayout />}>
            <Route index element={<Home />} />
          </Route>
        </Route>
      </Routes>
    </>
  );
}

export default App;
