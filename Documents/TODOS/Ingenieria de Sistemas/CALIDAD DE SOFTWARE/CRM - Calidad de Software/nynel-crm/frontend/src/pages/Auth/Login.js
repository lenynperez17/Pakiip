import React, { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import {
  Box,
  Paper,
  TextField,
  Button,
  Typography,
  Alert,
  InputAdornment,
  IconButton,
  Card,
  CardContent,
  Grid,
  Avatar,
  CircularProgress
} from '@mui/material';
import {
  Visibility,
  VisibilityOff,
  Person as PersonIcon,
  Lock as LockIcon,
  Business as BusinessIcon
} from '@mui/icons-material';
import { useAuth } from '../../hooks/useAuth';

function Login() {
  const [formData, setFormData] = useState({
    username: '',
    password: ''
  });
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState('');
  
  const navigate = useNavigate();
  const location = useLocation();
  const { login, loading, isAuthenticated } = useAuth();
  
  const from = location.state?.from?.pathname || '/';

  // Redirect if already authenticated
  useEffect(() => {
    if (isAuthenticated) {
      navigate(from, { replace: true });
    }
  }, [isAuthenticated, navigate, from]);

  const handleChange = (field) => (event) => {
    setFormData(prev => ({
      ...prev,
      [field]: event.target.value
    }));
    setError('');
  };

  const handleSubmit = async (event) => {
    event.preventDefault();
    
    if (!formData.username || !formData.password) {
      setError('Por favor ingresa usuario y contraseña');
      return;
    }

    try {
      setError('');
      await login(formData.username, formData.password);
      navigate(from, { replace: true });
    } catch (error) {
      setError(error.message || 'Error al iniciar sesión');
    }
  };

  const quickLogin = (username, password) => {
    setFormData({ username, password });
  };

  return (
    <Box
      sx={{
        minHeight: '100vh',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        p: 2
      }}
    >
      <Grid container spacing={4} maxWidth="1200px">
        <Grid item xs={12} md={6}>
          <Paper
            elevation={24}
            sx={{
              p: 4,
              borderRadius: 3,
              background: 'rgba(255, 255, 255, 0.95)',
              backdropFilter: 'blur(10px)'
            }}
          >
            <Box sx={{ textAlign: 'center', mb: 4 }}>
              <Avatar
                sx={{
                  width: 80,
                  height: 80,
                  margin: '0 auto 16px',
                  bgcolor: 'primary.main'
                }}
              >
                <BusinessIcon sx={{ fontSize: 40 }} />
              </Avatar>
              
              <Typography variant="h4" fontWeight="bold" color="primary" gutterBottom>
                NYNEL CRM
              </Typography>
              
              <Typography variant="body1" color="textSecondary">
                Sistema de Gestión de Relaciones con Clientes
              </Typography>
            </Box>

            {error && (
              <Alert severity="error" sx={{ mb: 3 }}>
                {error}
              </Alert>
            )}

            <form onSubmit={handleSubmit}>
              <TextField
                fullWidth
                label="Usuario"
                value={formData.username}
                onChange={handleChange('username')}
                margin="normal"
                InputProps={{
                  startAdornment: (
                    <InputAdornment position="start">
                      <PersonIcon color="action" />
                    </InputAdornment>
                  ),
                }}
                autoComplete="username"
              />

              <TextField
                fullWidth
                label="Contraseña"
                type={showPassword ? 'text' : 'password'}
                value={formData.password}
                onChange={handleChange('password')}
                margin="normal"
                InputProps={{
                  startAdornment: (
                    <InputAdornment position="start">
                      <LockIcon color="action" />
                    </InputAdornment>
                  ),
                  endAdornment: (
                    <InputAdornment position="end">
                      <IconButton
                        onClick={() => setShowPassword(!showPassword)}
                        edge="end"
                      >
                        {showPassword ? <VisibilityOff /> : <Visibility />}
                      </IconButton>
                    </InputAdornment>
                  ),
                }}
                autoComplete="current-password"
              />

              <Button
                type="submit"
                fullWidth
                variant="contained"
                size="large"
                disabled={loading}
                sx={{ mt: 3, mb: 2, py: 1.5 }}
              >
                {loading ? 'Iniciando sesión...' : 'Iniciar Sesión'}
              </Button>
            </form>

            <Typography variant="body2" color="textSecondary" align="center">
              v2.0.0 - Desarrollado para Calidad de Software
            </Typography>
          </Paper>
        </Grid>

        <Grid item xs={12} md={6}>
          <Box sx={{ color: 'white' }}>
            <Typography variant="h5" fontWeight="bold" gutterBottom>
              Acceso Rápido - Cuentas Demo
            </Typography>
            
            <Typography variant="body1" sx={{ mb: 3, opacity: 0.9 }}>
              Usa las siguientes credenciales para acceder a diferentes roles:
            </Typography>

            <Grid container spacing={2}>
              <Grid item xs={12} sm={6}>
                <Card 
                  sx={{ 
                    cursor: 'pointer',
                    transition: 'transform 0.2s',
                    '&:hover': { transform: 'translateY(-2px)' }
                  }}
                  onClick={() => quickLogin('admin', 'admin123')}
                >
                  <CardContent>
                    <Typography variant="h6" color="primary" gutterBottom>
                      👤 Administrador
                    </Typography>
                    <Typography variant="body2">
                      Usuario: <strong>admin</strong><br/>
                      Contraseña: <strong>admin123</strong>
                    </Typography>
                    <Typography variant="caption" color="textSecondary">
                      Acceso completo al sistema
                    </Typography>
                  </CardContent>
                </Card>
              </Grid>

              <Grid item xs={12} sm={6}>
                <Card 
                  sx={{ 
                    cursor: 'pointer',
                    transition: 'transform 0.2s',
                    '&:hover': { transform: 'translateY(-2px)' }
                  }}
                  onClick={() => quickLogin('vendedor1', 'vendedor123')}
                >
                  <CardContent>
                    <Typography variant="h6" color="primary" gutterBottom>
                      💼 Vendedor
                    </Typography>
                    <Typography variant="body2">
                      Usuario: <strong>vendedor1</strong><br/>
                      Contraseña: <strong>vendedor123</strong>
                    </Typography>
                    <Typography variant="caption" color="textSecondary">
                      Gestión de oportunidades
                    </Typography>
                  </CardContent>
                </Card>
              </Grid>

              <Grid item xs={12} sm={6}>
                <Card 
                  sx={{ 
                    cursor: 'pointer',
                    transition: 'transform 0.2s',
                    '&:hover': { transform: 'translateY(-2px)' }
                  }}
                  onClick={() => quickLogin('marketing1', 'marketing123')}
                >
                  <CardContent>
                    <Typography variant="h6" color="primary" gutterBottom>
                      📧 Marketing
                    </Typography>
                    <Typography variant="body2">
                      Usuario: <strong>marketing1</strong><br/>
                      Contraseña: <strong>marketing123</strong>
                    </Typography>
                    <Typography variant="caption" color="textSecondary">
                      Campañas y leads
                    </Typography>
                  </CardContent>
                </Card>
              </Grid>

              <Grid item xs={12} sm={6}>
                <Card 
                  sx={{ 
                    cursor: 'pointer',
                    transition: 'transform 0.2s',
                    '&:hover': { transform: 'translateY(-2px)' }
                  }}
                  onClick={() => quickLogin('soporte1', 'soporte123')}
                >
                  <CardContent>
                    <Typography variant="h6" color="primary" gutterBottom>
                      🎧 Soporte
                    </Typography>
                    <Typography variant="body2">
                      Usuario: <strong>soporte1</strong><br/>
                      Contraseña: <strong>soporte123</strong>
                    </Typography>
                    <Typography variant="caption" color="textSecondary">
                      Tickets y atención
                    </Typography>
                  </CardContent>
                </Card>
              </Grid>
            </Grid>

            <Typography variant="h6" gutterBottom sx={{ mt: 3 }}>
              🚀 Funcionalidades Principales
            </Typography>
            
            <Typography variant="body2" sx={{ opacity: 0.9 }}>
              • Gestión completa de contactos y cuentas<br/>
              • Pipeline de oportunidades con seguimiento<br/>
              • Campañas de marketing y generación de leads<br/>
              • Sistema de tickets de soporte<br/>
              • Reportes y análisis en tiempo real<br/>
              • Panel administrativo completo
            </Typography>
          </Box>
        </Grid>
      </Grid>
    </Box>
  );
};

export default Login;