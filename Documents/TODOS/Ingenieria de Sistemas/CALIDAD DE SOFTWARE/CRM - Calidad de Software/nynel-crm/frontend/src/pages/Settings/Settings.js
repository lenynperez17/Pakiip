import React, { useState, useEffect } from 'react';
import {
  Container,
  Grid,
  Card,
  CardContent,
  Typography,
  Box,
  TextField,
  Button,
  Switch,
  FormControlLabel,
  Divider,
  Select,
  MenuItem,
  FormControl,
  InputLabel,
  Alert,
  Snackbar,
  Tabs,
  Tab,
  IconButton,
  List,
  ListItem,
  ListItemText,
  ListItemSecondaryAction,
  Paper
} from '@mui/material';
import {
  Settings as SettingsIcon,
  Notifications,
  Security,
  Language,
  ColorLens,
  Email,
  Phone,
  Business,
  Save,
  Cancel,
  Delete,
  Add
} from '@mui/icons-material';
import { useAuth } from '../../hooks/useAuth';
import api from '../../services/api';

function TabPanel(props) {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`settings-tabpanel-${index}`}
      aria-labelledby={`settings-tab-${index}`}
      {...other}
    >
      {value === index && (
        <Box sx={{ p: 3 }}>
          {children}
        </Box>
      )}
    </div>
  );
}

const Settings = () => {
  const { user } = useAuth();
  const [tabValue, setTabValue] = useState(0);
  const [loading, setLoading] = useState(false);
  const [successMessage, setSuccessMessage] = useState('');
  const [errorMessage, setErrorMessage] = useState('');

  // General Settings State
  const [generalSettings, setGeneralSettings] = useState({
    companyName: 'Nynel CRM',
    companyEmail: 'info@nynelcrm.com',
    companyPhone: '+1234567890',
    companyAddress: '',
    timeZone: 'America/New_York',
    dateFormat: 'MM/DD/YYYY',
    currency: 'USD',
    language: 'es'
  });

  // Notification Settings State
  const [notificationSettings, setNotificationSettings] = useState({
    emailNotifications: true,
    newContact: true,
    newOpportunity: true,
    opportunityStatusChange: true,
    newTicket: true,
    ticketAssignment: true,
    dailyReport: false,
    weeklyReport: true,
    monthlyReport: true
  });

  // Theme Settings State
  const [themeSettings, setThemeSettings] = useState({
    darkMode: false,
    primaryColor: '#1976d2',
    secondaryColor: '#dc004e',
    fontSize: 'medium'
  });

  // Email Templates State
  const [emailTemplates, setEmailTemplates] = useState([
    {
      id: 1,
      name: 'Bienvenida',
      subject: 'Bienvenido a {{company_name}}',
      content: 'Estimado {{contact_name}}, ...'
    },
    {
      id: 2,
      name: 'Seguimiento',
      subject: 'Seguimiento - {{opportunity_name}}',
      content: 'Estimado {{contact_name}}, ...'
    }
  ]);

  useEffect(() => {
    loadSettings();
  }, []);

  const loadSettings = async () => {
    try {
      const response = await api.get('/api/settings/');
      if (response.data) {
        setGeneralSettings(response.data.general || generalSettings);
        setNotificationSettings(response.data.notifications || notificationSettings);
        setThemeSettings(response.data.theme || themeSettings);
        setEmailTemplates(response.data.emailTemplates || emailTemplates);
      }
    } catch (error) {
      console.error('Error loading settings:', error);
    }
  };

  const handleTabChange = (event, newValue) => {
    setTabValue(newValue);
  };

  const handleGeneralSettingChange = (field) => (event) => {
    setGeneralSettings({
      ...generalSettings,
      [field]: event.target.value
    });
  };

  const handleNotificationChange = (setting) => (event) => {
    setNotificationSettings({
      ...notificationSettings,
      [setting]: event.target.checked
    });
  };

  const handleThemeChange = (setting) => (event) => {
    const value = setting === 'darkMode' ? event.target.checked : event.target.value;
    setThemeSettings({
      ...themeSettings,
      [setting]: value
    });
  };

  const saveSettings = async () => {
    setLoading(true);
    try {
      await api.post('/api/settings/', {
        general: generalSettings,
        notifications: notificationSettings,
        theme: themeSettings,
        emailTemplates: emailTemplates
      });
      setSuccessMessage('Configuración guardada exitosamente');
    } catch (error) {
      setErrorMessage('Error al guardar la configuración');
      console.error('Error saving settings:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleCloseSnackbar = () => {
    setSuccessMessage('');
    setErrorMessage('');
  };

  return (
    <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
      <Box sx={{ display: 'flex', alignItems: 'center', mb: 3 }}>
        <SettingsIcon sx={{ mr: 2, fontSize: 30 }} />
        <Typography variant="h4" component="h1">
          Configuración
        </Typography>
      </Box>

      <Paper sx={{ width: '100%' }}>
        <Tabs
          value={tabValue}
          onChange={handleTabChange}
          indicatorColor="primary"
          textColor="primary"
          variant="scrollable"
          scrollButtons="auto"
        >
          <Tab label="General" icon={<Business />} iconPosition="start" />
          <Tab label="Notificaciones" icon={<Notifications />} iconPosition="start" />
          <Tab label="Apariencia" icon={<ColorLens />} iconPosition="start" />
          <Tab label="Plantillas de Email" icon={<Email />} iconPosition="start" />
          <Tab label="Seguridad" icon={<Security />} iconPosition="start" />
        </Tabs>

        <TabPanel value={tabValue} index={0}>
          <Grid container spacing={3}>
            <Grid item xs={12} md={6}>
              <TextField
                fullWidth
                label="Nombre de la Empresa"
                value={generalSettings.companyName}
                onChange={handleGeneralSettingChange('companyName')}
                variant="outlined"
                margin="normal"
              />
            </Grid>
            <Grid item xs={12} md={6}>
              <TextField
                fullWidth
                label="Email de la Empresa"
                value={generalSettings.companyEmail}
                onChange={handleGeneralSettingChange('companyEmail')}
                variant="outlined"
                margin="normal"
              />
            </Grid>
            <Grid item xs={12} md={6}>
              <TextField
                fullWidth
                label="Teléfono de la Empresa"
                value={generalSettings.companyPhone}
                onChange={handleGeneralSettingChange('companyPhone')}
                variant="outlined"
                margin="normal"
              />
            </Grid>
            <Grid item xs={12} md={6}>
              <TextField
                fullWidth
                label="Dirección de la Empresa"
                value={generalSettings.companyAddress}
                onChange={handleGeneralSettingChange('companyAddress')}
                variant="outlined"
                margin="normal"
              />
            </Grid>
            <Grid item xs={12} md={4}>
              <FormControl fullWidth margin="normal">
                <InputLabel>Zona Horaria</InputLabel>
                <Select
                  value={generalSettings.timeZone}
                  onChange={handleGeneralSettingChange('timeZone')}
                >
                  <MenuItem value="America/New_York">Eastern (EST)</MenuItem>
                  <MenuItem value="America/Chicago">Central (CST)</MenuItem>
                  <MenuItem value="America/Denver">Mountain (MST)</MenuItem>
                  <MenuItem value="America/Los_Angeles">Pacific (PST)</MenuItem>
                  <MenuItem value="America/Lima">Lima (PET)</MenuItem>
                  <MenuItem value="America/Mexico_City">Ciudad de México</MenuItem>
                </Select>
              </FormControl>
            </Grid>
            <Grid item xs={12} md={4}>
              <FormControl fullWidth margin="normal">
                <InputLabel>Formato de Fecha</InputLabel>
                <Select
                  value={generalSettings.dateFormat}
                  onChange={handleGeneralSettingChange('dateFormat')}
                >
                  <MenuItem value="MM/DD/YYYY">MM/DD/YYYY</MenuItem>
                  <MenuItem value="DD/MM/YYYY">DD/MM/YYYY</MenuItem>
                  <MenuItem value="YYYY-MM-DD">YYYY-MM-DD</MenuItem>
                </Select>
              </FormControl>
            </Grid>
            <Grid item xs={12} md={4}>
              <FormControl fullWidth margin="normal">
                <InputLabel>Moneda</InputLabel>
                <Select
                  value={generalSettings.currency}
                  onChange={handleGeneralSettingChange('currency')}
                >
                  <MenuItem value="USD">USD ($)</MenuItem>
                  <MenuItem value="EUR">EUR (€)</MenuItem>
                  <MenuItem value="GBP">GBP (£)</MenuItem>
                  <MenuItem value="MXN">MXN ($)</MenuItem>
                  <MenuItem value="PEN">PEN (S/)</MenuItem>
                </Select>
              </FormControl>
            </Grid>
          </Grid>
        </TabPanel>

        <TabPanel value={tabValue} index={1}>
          <Typography variant="h6" gutterBottom>
            Notificaciones por Email
          </Typography>
          <Divider sx={{ mb: 2 }} />
          
          <FormControlLabel
            control={
              <Switch
                checked={notificationSettings.emailNotifications}
                onChange={handleNotificationChange('emailNotifications')}
              />
            }
            label="Activar notificaciones por email"
          />

          <Box sx={{ ml: 4, mt: 2 }}>
            <Typography variant="subtitle2" gutterBottom>
              Contactos
            </Typography>
            <FormControlLabel
              control={
                <Switch
                  checked={notificationSettings.newContact}
                  onChange={handleNotificationChange('newContact')}
                  disabled={!notificationSettings.emailNotifications}
                />
              }
              label="Nuevo contacto agregado"
            />

            <Typography variant="subtitle2" gutterBottom sx={{ mt: 2 }}>
              Oportunidades
            </Typography>
            <FormControlLabel
              control={
                <Switch
                  checked={notificationSettings.newOpportunity}
                  onChange={handleNotificationChange('newOpportunity')}
                  disabled={!notificationSettings.emailNotifications}
                />
              }
              label="Nueva oportunidad creada"
            />
            <FormControlLabel
              control={
                <Switch
                  checked={notificationSettings.opportunityStatusChange}
                  onChange={handleNotificationChange('opportunityStatusChange')}
                  disabled={!notificationSettings.emailNotifications}
                />
              }
              label="Cambio de estado en oportunidad"
            />

            <Typography variant="subtitle2" gutterBottom sx={{ mt: 2 }}>
              Tickets
            </Typography>
            <FormControlLabel
              control={
                <Switch
                  checked={notificationSettings.newTicket}
                  onChange={handleNotificationChange('newTicket')}
                  disabled={!notificationSettings.emailNotifications}
                />
              }
              label="Nuevo ticket creado"
            />
            <FormControlLabel
              control={
                <Switch
                  checked={notificationSettings.ticketAssignment}
                  onChange={handleNotificationChange('ticketAssignment')}
                  disabled={!notificationSettings.emailNotifications}
                />
              }
              label="Ticket asignado a ti"
            />

            <Typography variant="subtitle2" gutterBottom sx={{ mt: 2 }}>
              Reportes
            </Typography>
            <FormControlLabel
              control={
                <Switch
                  checked={notificationSettings.dailyReport}
                  onChange={handleNotificationChange('dailyReport')}
                  disabled={!notificationSettings.emailNotifications}
                />
              }
              label="Reporte diario"
            />
            <FormControlLabel
              control={
                <Switch
                  checked={notificationSettings.weeklyReport}
                  onChange={handleNotificationChange('weeklyReport')}
                  disabled={!notificationSettings.emailNotifications}
                />
              }
              label="Reporte semanal"
            />
            <FormControlLabel
              control={
                <Switch
                  checked={notificationSettings.monthlyReport}
                  onChange={handleNotificationChange('monthlyReport')}
                  disabled={!notificationSettings.emailNotifications}
                />
              }
              label="Reporte mensual"
            />
          </Box>
        </TabPanel>

        <TabPanel value={tabValue} index={2}>
          <Typography variant="h6" gutterBottom>
            Personalización de la Interfaz
          </Typography>
          <Divider sx={{ mb: 2 }} />
          
          <Grid container spacing={3}>
            <Grid item xs={12}>
              <FormControlLabel
                control={
                  <Switch
                    checked={themeSettings.darkMode}
                    onChange={handleThemeChange('darkMode')}
                  />
                }
                label="Modo Oscuro"
              />
            </Grid>
            
            <Grid item xs={12} md={6}>
              <Typography variant="subtitle2" gutterBottom>
                Color Primario
              </Typography>
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                <input
                  type="color"
                  value={themeSettings.primaryColor}
                  onChange={handleThemeChange('primaryColor')}
                  style={{ width: 50, height: 40, cursor: 'pointer' }}
                />
                <TextField
                  value={themeSettings.primaryColor}
                  onChange={handleThemeChange('primaryColor')}
                  size="small"
                />
              </Box>
            </Grid>
            
            <Grid item xs={12} md={6}>
              <Typography variant="subtitle2" gutterBottom>
                Color Secundario
              </Typography>
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                <input
                  type="color"
                  value={themeSettings.secondaryColor}
                  onChange={handleThemeChange('secondaryColor')}
                  style={{ width: 50, height: 40, cursor: 'pointer' }}
                />
                <TextField
                  value={themeSettings.secondaryColor}
                  onChange={handleThemeChange('secondaryColor')}
                  size="small"
                />
              </Box>
            </Grid>

            <Grid item xs={12} md={6}>
              <FormControl fullWidth>
                <InputLabel>Tamaño de Fuente</InputLabel>
                <Select
                  value={themeSettings.fontSize}
                  onChange={handleThemeChange('fontSize')}
                >
                  <MenuItem value="small">Pequeño</MenuItem>
                  <MenuItem value="medium">Mediano</MenuItem>
                  <MenuItem value="large">Grande</MenuItem>
                </Select>
              </FormControl>
            </Grid>
          </Grid>
        </TabPanel>

        <TabPanel value={tabValue} index={3}>
          <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
            <Typography variant="h6">
              Plantillas de Email
            </Typography>
            <Button
              variant="contained"
              startIcon={<Add />}
              size="small"
            >
              Nueva Plantilla
            </Button>
          </Box>
          <Divider sx={{ mb: 2 }} />
          
          <List>
            {emailTemplates.map((template) => (
              <React.Fragment key={template.id}>
                <ListItem>
                  <ListItemText
                    primary={template.name}
                    secondary={template.subject}
                  />
                  <ListItemSecondaryAction>
                    <IconButton edge="end" aria-label="edit">
                      <SettingsIcon />
                    </IconButton>
                    <IconButton edge="end" aria-label="delete">
                      <Delete />
                    </IconButton>
                  </ListItemSecondaryAction>
                </ListItem>
                <Divider />
              </React.Fragment>
            ))}
          </List>

          <Box sx={{ mt: 3, p: 2, bgcolor: 'grey.100', borderRadius: 1 }}>
            <Typography variant="body2" color="text.secondary">
              Variables disponibles: &#123;&#123;contact_name&#125;&#125;, &#123;&#123;company_name&#125;&#125;, &#123;&#123;opportunity_name&#125;&#125;, &#123;&#123;user_name&#125;&#125;
            </Typography>
          </Box>
        </TabPanel>

        <TabPanel value={tabValue} index={4}>
          <Typography variant="h6" gutterBottom>
            Configuración de Seguridad
          </Typography>
          <Divider sx={{ mb: 2 }} />
          
          <Grid container spacing={3}>
            <Grid item xs={12}>
              <Card variant="outlined">
                <CardContent>
                  <Typography variant="subtitle1" gutterBottom>
                    Política de Contraseñas
                  </Typography>
                  <FormControlLabel
                    control={<Switch defaultChecked />}
                    label="Requerir contraseña fuerte (mínimo 8 caracteres, mayúsculas, números)"
                  />
                  <FormControlLabel
                    control={<Switch />}
                    label="Expiración de contraseña (90 días)"
                  />
                  <FormControlLabel
                    control={<Switch />}
                    label="Requerir cambio de contraseña en primer inicio"
                  />
                </CardContent>
              </Card>
            </Grid>

            <Grid item xs={12}>
              <Card variant="outlined">
                <CardContent>
                  <Typography variant="subtitle1" gutterBottom>
                    Autenticación de Dos Factores
                  </Typography>
                  <FormControlLabel
                    control={<Switch />}
                    label="Habilitar 2FA para todos los usuarios"
                  />
                  <FormControlLabel
                    control={<Switch />}
                    label="Requerir 2FA para administradores"
                  />
                </CardContent>
              </Card>
            </Grid>

            <Grid item xs={12}>
              <Card variant="outlined">
                <CardContent>
                  <Typography variant="subtitle1" gutterBottom>
                    Sesiones
                  </Typography>
                  <Grid container spacing={2}>
                    <Grid item xs={12} md={6}>
                      <TextField
                        fullWidth
                        type="number"
                        label="Tiempo de inactividad (minutos)"
                        defaultValue="30"
                        InputProps={{ inputProps: { min: 5, max: 480 } }}
                      />
                    </Grid>
                    <Grid item xs={12} md={6}>
                      <TextField
                        fullWidth
                        type="number"
                        label="Máximo de sesiones concurrentes"
                        defaultValue="3"
                        InputProps={{ inputProps: { min: 1, max: 10 } }}
                      />
                    </Grid>
                  </Grid>
                </CardContent>
              </Card>
            </Grid>
          </Grid>
        </TabPanel>

        <Box sx={{ p: 3, display: 'flex', justifyContent: 'flex-end', gap: 2 }}>
          <Button
            variant="outlined"
            startIcon={<Cancel />}
            onClick={() => loadSettings()}
          >
            Cancelar
          </Button>
          <Button
            variant="contained"
            startIcon={<Save />}
            onClick={saveSettings}
            disabled={loading}
          >
            Guardar Cambios
          </Button>
        </Box>
      </Paper>

      <Snackbar
        open={!!successMessage}
        autoHideDuration={6000}
        onClose={handleCloseSnackbar}
      >
        <Alert onClose={handleCloseSnackbar} severity="success">
          {successMessage}
        </Alert>
      </Snackbar>

      <Snackbar
        open={!!errorMessage}
        autoHideDuration={6000}
        onClose={handleCloseSnackbar}
      >
        <Alert onClose={handleCloseSnackbar} severity="error">
          {errorMessage}
        </Alert>
      </Snackbar>
    </Container>
  );
};

export default Settings;