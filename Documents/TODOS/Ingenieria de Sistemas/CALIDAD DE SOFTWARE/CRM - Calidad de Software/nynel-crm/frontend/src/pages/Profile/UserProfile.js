import React, { useState } from 'react';
import {
  Box,
  Paper,
  Typography,
  Button,
  TextField,
  Grid,
  Avatar,
  Card,
  CardContent,
  Divider,
  Alert,
  IconButton,
  InputAdornment,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Chip
} from '@mui/material';
import {
  Edit as EditIcon,
  Save as SaveIcon,
  Cancel as CancelIcon,
  Visibility,
  VisibilityOff,
  Person as PersonIcon,
  Email as EmailIcon,
  Phone as PhoneIcon,
  Business as BusinessIcon,
  Security as SecurityIcon,
  AccessTime as TimeIcon
} from '@mui/icons-material';
import { useAuth } from '../../hooks/useAuth';

function UserProfile() {
  const { user } = useAuth();
  const [editMode, setEditMode] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [success, setSuccess] = useState('');
  const [error, setError] = useState('');

  const [profileData, setProfileData] = useState({
    first_name: user?.first_name || '',
    last_name: user?.last_name || '',
    email: user?.email || `${user?.username}@nynel.com`,
    phone: '+51 987 654 321',
    department: user?.role === 'admin' ? 'Administración' :
                user?.role === 'vendedor' ? 'Ventas' :
                user?.role === 'marketing' ? 'Marketing' :
                user?.role === 'soporte' ? 'Soporte' : 'General',
    job_title: user?.role === 'admin' ? 'Administrador del Sistema' :
               user?.role === 'vendedor' ? 'Ejecutivo de Ventas' :
               user?.role === 'marketing' ? 'Especialista en Marketing' :
               user?.role === 'soporte' ? 'Agente de Soporte' : 'Usuario',
    address: 'Av. Principal 123, Lima, Perú',
    bio: 'Usuario del sistema NYNEL CRM',
    current_password: '',
    new_password: '',
    confirm_password: ''
  });

  // Datos demo de actividad reciente
  const recentActivity = [
    { date: '2024-06-15 10:30', action: 'Inició sesión', type: 'login' },
    { date: '2024-06-15 09:15', action: 'Actualizó contacto: Luis Rodríguez', type: 'update' },
    { date: '2024-06-14 16:45', action: 'Creó oportunidad: Proyecto ERP', type: 'create' },
    { date: '2024-06-14 14:20', action: 'Resolvió ticket #TK-001', type: 'resolve' },
    { date: '2024-06-13 11:30', action: 'Inició sesión', type: 'login' }
  ];

  const handleChange = (field) => (event) => {
    setProfileData(prev => ({
      ...prev,
      [field]: event.target.value
    }));
    setError('');
  };

  const handleSave = () => {
    // Validaciones básicas
    if (!profileData.first_name || !profileData.last_name) {
      setError('El nombre y apellido son requeridos');
      return;
    }

    if (profileData.new_password && profileData.new_password !== profileData.confirm_password) {
      setError('Las contraseñas no coinciden');
      return;
    }

    if (profileData.new_password && profileData.new_password.length < 6) {
      setError('La nueva contraseña debe tener al menos 6 caracteres');
      return;
    }

    // Simular guardado
    setSuccess('Perfil actualizado correctamente');
    setEditMode(false);
    setError('');
    
    // Limpiar campos de contraseña
    setProfileData(prev => ({
      ...prev,
      current_password: '',
      new_password: '',
      confirm_password: ''
    }));

    setTimeout(() => setSuccess(''), 3000);
  };

  const handleCancel = () => {
    setEditMode(false);
    setError('');
    setProfileData(prev => ({
      ...prev,
      current_password: '',
      new_password: '',
      confirm_password: ''
    }));
  };

  const getActivityIcon = (type) => {
    switch (type) {
      case 'login': return <SecurityIcon />;
      case 'create': return <PersonIcon />;
      case 'update': return <EditIcon />;
      case 'resolve': return <SaveIcon />;
      default: return <TimeIcon />;
    }
  };

  const getActivityColor = (type) => {
    switch (type) {
      case 'login': return 'primary';
      case 'create': return 'success';
      case 'update': return 'warning';
      case 'resolve': return 'info';
      default: return 'default';
    }
  };

  return (
    <Box>
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
        <Typography variant="h4" fontWeight="bold">
          Mi Perfil
        </Typography>
        {!editMode ? (
          <Button
            variant="contained"
            startIcon={<EditIcon />}
            onClick={() => setEditMode(true)}
          >
            Editar Perfil
          </Button>
        ) : (
          <Box>
            <Button
              variant="outlined"
              startIcon={<CancelIcon />}
              onClick={handleCancel}
              sx={{ mr: 1 }}
            >
              Cancelar
            </Button>
            <Button
              variant="contained"
              startIcon={<SaveIcon />}
              onClick={handleSave}
            >
              Guardar
            </Button>
          </Box>
        )}
      </Box>

      {success && (
        <Alert severity="success" sx={{ mb: 3 }}>
          {success}
        </Alert>
      )}

      {error && (
        <Alert severity="error" sx={{ mb: 3 }}>
          {error}
        </Alert>
      )}

      <Grid container spacing={3}>
        {/* Información Personal */}
        <Grid item xs={12} md={8}>
          <Paper sx={{ p: 3, mb: 3 }}>
            <Box display="flex" alignItems="center" mb={3}>
              <Avatar
                sx={{
                  width: 80,
                  height: 80,
                  mr: 3,
                  bgcolor: 'primary.main',
                  fontSize: '2rem'
                }}
              >
                {user?.first_name?.charAt(0)?.toUpperCase() || user?.username?.charAt(0)?.toUpperCase() || 'U'}
              </Avatar>
              <Box>
                <Typography variant="h5" fontWeight="bold">
                  {user?.first_name} {user?.last_name}
                </Typography>
                <Typography variant="body1" color="textSecondary">
                  {profileData.job_title}
                </Typography>
                <Typography variant="body2" color="textSecondary">
                  {profileData.department}
                </Typography>
              </Box>
            </Box>

            <Divider sx={{ mb: 3 }} />

            <Typography variant="h6" gutterBottom>
              Información Personal
            </Typography>

            <Grid container spacing={2}>
              <Grid item xs={12} sm={6}>
                <TextField
                  fullWidth
                  label="Nombre"
                  value={profileData.first_name}
                  onChange={handleChange('first_name')}
                  disabled={!editMode}
                  InputProps={{
                    startAdornment: (
                      <InputAdornment position="start">
                        <PersonIcon />
                      </InputAdornment>
                    ),
                  }}
                />
              </Grid>
              <Grid item xs={12} sm={6}>
                <TextField
                  fullWidth
                  label="Apellido"
                  value={profileData.last_name}
                  onChange={handleChange('last_name')}
                  disabled={!editMode}
                />
              </Grid>
              <Grid item xs={12} sm={6}>
                <TextField
                  fullWidth
                  label="Email"
                  value={profileData.email}
                  onChange={handleChange('email')}
                  disabled={!editMode}
                  InputProps={{
                    startAdornment: (
                      <InputAdornment position="start">
                        <EmailIcon />
                      </InputAdornment>
                    ),
                  }}
                />
              </Grid>
              <Grid item xs={12} sm={6}>
                <TextField
                  fullWidth
                  label="Teléfono"
                  value={profileData.phone}
                  onChange={handleChange('phone')}
                  disabled={!editMode}
                  InputProps={{
                    startAdornment: (
                      <InputAdornment position="start">
                        <PhoneIcon />
                      </InputAdornment>
                    ),
                  }}
                />
              </Grid>
              <Grid item xs={12} sm={6}>
                <TextField
                  fullWidth
                  label="Departamento"
                  value={profileData.department}
                  disabled={true}
                  InputProps={{
                    startAdornment: (
                      <InputAdornment position="start">
                        <BusinessIcon />
                      </InputAdornment>
                    ),
                  }}
                />
              </Grid>
              <Grid item xs={12} sm={6}>
                <TextField
                  fullWidth
                  label="Cargo"
                  value={profileData.job_title}
                  disabled={true}
                />
              </Grid>
              <Grid item xs={12}>
                <TextField
                  fullWidth
                  label="Dirección"
                  value={profileData.address}
                  onChange={handleChange('address')}
                  disabled={!editMode}
                />
              </Grid>
              <Grid item xs={12}>
                <TextField
                  fullWidth
                  label="Biografía"
                  value={profileData.bio}
                  onChange={handleChange('bio')}
                  disabled={!editMode}
                  multiline
                  rows={3}
                />
              </Grid>
            </Grid>

            {editMode && (
              <>
                <Divider sx={{ my: 3 }} />
                <Typography variant="h6" gutterBottom>
                  Cambiar Contraseña
                </Typography>
                <Grid container spacing={2}>
                  <Grid item xs={12}>
                    <TextField
                      fullWidth
                      label="Contraseña Actual"
                      type={showPassword ? 'text' : 'password'}
                      value={profileData.current_password}
                      onChange={handleChange('current_password')}
                      InputProps={{
                        startAdornment: (
                          <InputAdornment position="start">
                            <SecurityIcon />
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
                    />
                  </Grid>
                  <Grid item xs={12} sm={6}>
                    <TextField
                      fullWidth
                      label="Nueva Contraseña"
                      type="password"
                      value={profileData.new_password}
                      onChange={handleChange('new_password')}
                    />
                  </Grid>
                  <Grid item xs={12} sm={6}>
                    <TextField
                      fullWidth
                      label="Confirmar Nueva Contraseña"
                      type="password"
                      value={profileData.confirm_password}
                      onChange={handleChange('confirm_password')}
                    />
                  </Grid>
                </Grid>
              </>
            )}
          </Paper>
        </Grid>

        {/* Panel Lateral */}
        <Grid item xs={12} md={4}>
          {/* Información del Sistema */}
          <Card sx={{ mb: 3 }}>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                Información del Sistema
              </Typography>
              <Box sx={{ mb: 2 }}>
                <Typography variant="body2" color="textSecondary">
                  Usuario
                </Typography>
                <Typography variant="body1">
                  {user?.username}
                </Typography>
              </Box>
              <Box sx={{ mb: 2 }}>
                <Typography variant="body2" color="textSecondary">
                  Rol
                </Typography>
                <Chip 
                  label={user?.role || 'Usuario'}
                  color="primary"
                  size="small"
                />
              </Box>
              <Box sx={{ mb: 2 }}>
                <Typography variant="body2" color="textSecondary">
                  Último Acceso
                </Typography>
                <Typography variant="body1">
                  Hoy, 10:30 AM
                </Typography>
              </Box>
              <Box>
                <Typography variant="body2" color="textSecondary">
                  Sesiones Activas
                </Typography>
                <Typography variant="body1">
                  1 dispositivo
                </Typography>
              </Box>
            </CardContent>
          </Card>

          {/* Actividad Reciente */}
          <Card>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                Actividad Reciente
              </Typography>
              <TableContainer>
                <Table size="small">
                  <TableBody>
                    {recentActivity.map((activity, index) => (
                      <TableRow key={index}>
                        <TableCell padding="none">
                          <Chip
                            icon={getActivityIcon(activity.type)}
                            label=""
                            size="small"
                            color={getActivityColor(activity.type)}
                            variant="outlined"
                          />
                        </TableCell>
                        <TableCell>
                          <Typography variant="body2">
                            {activity.action}
                          </Typography>
                          <Typography variant="caption" color="textSecondary">
                            {activity.date}
                          </Typography>
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </TableContainer>
            </CardContent>
          </Card>
        </Grid>
      </Grid>
    </Box>
  );
}

export default UserProfile;