import React, { useState, useEffect, createContext, useContext, useCallback } from 'react';

// Create Auth Context
const AuthContext = createContext({
  user: null,
  isAuthenticated: false,
  loading: true,
  login: async () => {},
  logout: () => {},
});

// Auth Provider Component
export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [isAuthenticated, setIsAuthenticated] = useState(false);

  // Check if user is authenticated on mount
  useEffect(() => {
    checkAuth();
  }, []);

  // Check authentication status
  const checkAuth = () => {
    const token = localStorage.getItem('auth_token');
    const userData = localStorage.getItem('user_data');
    
    
    if (!token || !userData) {
      setLoading(false);
      setIsAuthenticated(false);
      setUser(null);
      return;
    }

    try {
      // Parse stored user data for demo
      const parsedUser = JSON.parse(userData);
      setUser(parsedUser);
      setIsAuthenticated(true);
    } catch (error) {
      console.error('Auth check failed:', error);
      handleLogout();
    } finally {
      setLoading(false);
    }
  };

  // Login function
  const login = async (username, password) => {
    
    try {
      setLoading(true);
      
      // Demo authentication
      if (
        (username === 'admin' && password === 'admin123') ||
        (username === 'vendedor1' && password === 'vendedor123') ||
        (username === 'marketing1' && password === 'marketing123') ||
        (username === 'soporte1' && password === 'soporte123')
      ) {
        const userData = {
          username,
          role: username.includes('admin') ? 'admin' : username.split(/\d+/)[0],
          name: username === 'admin' ? 'Administrador Sistema' :
                username === 'vendedor1' ? 'Carlos Mendoza' :
                username === 'marketing1' ? 'María García' :
                username === 'soporte1' ? 'Juan Pérez' : username,
          first_name: username === 'admin' ? 'Admin' :
                     username === 'vendedor1' ? 'Carlos' :
                     username === 'marketing1' ? 'María' :
                     username === 'soporte1' ? 'Juan' : username,
          last_name: username === 'admin' ? 'Sistema' :
                    username === 'vendedor1' ? 'Mendoza' :
                    username === 'marketing1' ? 'García' :
                    username === 'soporte1' ? 'Pérez' : ''
        };


        // Store tokens
        localStorage.setItem('auth_token', 'demo_token_' + username);
        localStorage.setItem('user_data', JSON.stringify(userData));

        // Update state
        setUser(userData);
        setIsAuthenticated(true);

        return userData;
      } else {
        throw new Error('Credenciales inválidas');
      }
    } catch (error) {
      console.error('Login error:', error);
      throw error;
    } finally {
      setLoading(false);
    }
  };

  // Handle logout (clear auth state)
  const handleLogout = () => {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user_data');
    setUser(null);
    setIsAuthenticated(false);
  };

  // Logout function
  const logout = useCallback(() => {
    handleLogout();
  }, []);

  const value = {
    user,
    isAuthenticated,
    loading,
    login,
    logout
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};

// Custom hook to use auth context
export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};