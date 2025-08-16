import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { ThemeProvider, createTheme } from '@mui/material/styles';
import CssBaseline from '@mui/material/CssBaseline';
import { Provider } from 'react-redux';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import { AdapterDateFns } from '@mui/x-date-pickers/AdapterDateFns';
import { es } from 'date-fns/locale';

import { store } from './store/store';
import { AuthProvider } from './hooks/useAuth';
import ProtectedRoute from './components/ProtectedRoute';
import Layout from './components/Layout/Layout';
import Dashboard from './pages/Dashboard/Dashboard';
import Login from './pages/Auth/Login';
import ContactsList from './pages/Contacts/ContactsList';
import OpportunitiesList from './pages/Opportunities/OpportunitiesList';
import MarketingDashboard from './pages/Marketing/MarketingDashboard';
import TicketsList from './pages/Tickets/TicketsList';
import ReportsDashboard from './pages/Reports/ReportsDashboard';
import ActivitiesList from './pages/Activities/ActivitiesList';
import UserProfile from './pages/Profile/UserProfile';
import Settings from './pages/Settings/Settings';

// Create Material-UI theme
const theme = createTheme({
  palette: {
    primary: {
      main: '#1976d2',
      light: '#42a5f5',
      dark: '#1565c0',
    },
    secondary: {
      main: '#dc004e',
    },
    background: {
      default: '#f5f5f5',
    },
  },
  typography: {
    fontFamily: '"Roboto", "Helvetica", "Arial", sans-serif',
    h6: {
      fontWeight: 600,
    },
  },
  components: {
    MuiAppBar: {
      styleOverrides: {
        root: {
          boxShadow: '0 1px 3px rgba(0,0,0,0.12)',
        },
      },
    },
    MuiCard: {
      styleOverrides: {
        root: {
          boxShadow: '0 2px 4px rgba(0,0,0,0.1)',
          borderRadius: '8px',
        },
      },
    },
  },
});

function App() {
  return (
    <Provider store={store}>
      <ThemeProvider theme={theme}>
        <LocalizationProvider dateAdapter={AdapterDateFns} adapterLocale={es}>
          <CssBaseline />
          <Router>
            <AuthProvider>
              <Routes>
                {/* Public routes */}
                <Route path="/login" element={<Login />} />
                
                {/* Protected routes with Layout wrapper */}
                <Route
                  path="/"
                  element={
                    <ProtectedRoute>
                      <Layout />
                    </ProtectedRoute>
                  }
                >
                  {/* Nested routes within Layout */}
                  <Route index element={<Dashboard />} />
                  <Route path="dashboard" element={<Dashboard />} />
                  <Route path="contacts" element={<ContactsList />} />
                  <Route path="accounts" element={<ContactsList />} />
                  <Route path="opportunities" element={<OpportunitiesList />} />
                  <Route path="marketing" element={<MarketingDashboard />} />
                  <Route path="marketing/campaigns" element={<MarketingDashboard />} />
                  <Route path="tickets" element={<TicketsList />} />
                  <Route path="reports" element={<ReportsDashboard />} />
                  <Route path="activities" element={<ActivitiesList />} />
                  <Route path="profile" element={<UserProfile />} />
                  <Route path="settings" element={<Settings />} />
                </Route>
                
                {/* Catch all - redirect to home */}
                <Route path="*" element={<Navigate to="/" replace />} />
              </Routes>
            </AuthProvider>
          </Router>
        </LocalizationProvider>
      </ThemeProvider>
    </Provider>
  );
}

export default App;