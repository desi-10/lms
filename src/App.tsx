import { Routes, Route } from "react-router-dom";

import RootLayout from "./layouts/RootLayout";
import Home from "./pages/Home";
import Login from "./pages/Login";
import RestrictedRoute from "./layouts/RestrictedRoute";
import ProtectedRoute from "./layouts/ProtectedRoute";
import SharedLayout from "./layouts/SharedLayout";
import Video from "./pages/Video";
import Notes from "./pages/Notes";
import Chat from "./pages/Chat";
import CoursePage from "./pages/CoursePage";
import CourseDetails from "./pages/CourseDetails";

function App() {
  return (
    <>
      <Routes>
        <Route element={<RestrictedRoute />}>
          <Route index element={<Login />} />
        </Route>
        <Route element={<ProtectedRoute />}>
          <Route path="/dashboard" element={<RootLayout />}>
            <Route element={<SharedLayout />}>
              <Route index element={<Home />} />
              <Route path="video" element={<Video />} />
              <Route path="notes" element={<Notes />} />
              <Route path="chat" element={<Chat />} />
              <Route path="coursepage" element={<CoursePage />} />
              <Route
                path="coursepage/coursedetails"
                element={<CourseDetails />}
              />
            </Route>
          </Route>
        </Route>
      </Routes>
    </>
  );
}

export default App;
