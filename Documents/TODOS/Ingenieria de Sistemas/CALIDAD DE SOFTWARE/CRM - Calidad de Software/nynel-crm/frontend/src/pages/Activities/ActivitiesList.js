import React, { useState, useEffect } from 'react';
import {
  Box,
  Paper,
  Typography,
  Button,
  IconButton,
  Chip,
  Avatar,
  Menu,
  MenuItem,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  Grid,
  Card,
  CardContent,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  TablePagination,
  InputAdornment,
  Fab,
  FormControl,
  InputLabel,
  Select,
  Tabs,
  Tab
} from '@mui/material';
import {
  Add as AddIcon,
  Search as SearchIcon,
  MoreVert as MoreVertIcon,
  Phone as PhoneIcon,
  Email as EmailIcon,
  Event as EventIcon,
  Assignment as TaskIcon,
  Edit as EditIcon,
  Delete as DeleteIcon,
  Visibility as ViewIcon,
  CheckCircle as CheckCircleIcon,
  Schedule as ScheduleIcon,
  Person as PersonIcon,
  Business as BusinessIcon
} from '@mui/icons-material';

const activityTypes = {
  'call': { label: 'Llamada', icon: <PhoneIcon />, color: 'primary' },
  'email': { label: 'Email', icon: <EmailIcon />, color: 'secondary' },
  'meeting': { label: 'Reunión', icon: <EventIcon />, color: 'success' },
  'task': { label: 'Tarea', icon: <TaskIcon />, color: 'warning' },
  'note': { label: 'Nota', icon: <EditIcon />, color: 'info' }
};

const activityStatuses = {
  'pending': { label: 'Pendiente', color: 'warning' },
  'in_progress': { label: 'En Progreso', color: 'info' },
  'completed': { label: 'Completado', color: 'success' },
  'cancelled': { label: 'Cancelado', color: 'error' }
};

// Datos demo de actividades
const demoActivities = [
  {
    id: 1,
    title: 'Llamada de seguimiento - Tech Solutions',
    type: 'call',
    status: 'pending',
    due_date: '2024-06-15T10:00:00',
    contact_name: 'Luis Rodríguez',
    account_name: 'Tech Solutions SAC',
    assigned_to: 'Carlos Mendoza',
    description: 'Seguimiento de propuesta ERP, discutir términos finales',
    priority: 'high'
  },
  {
    id: 2,
    title: 'Reunión demo producto - Innovate Corp',
    type: 'meeting',
    status: 'completed',
    due_date: '2024-06-14T14:30:00',
    contact_name: 'Ana Torres',
    account_name: 'Innovate Corp EIRL',
    assigned_to: 'María García',
    description: 'Demostración del CRM y funcionalidades principales',
    priority: 'medium'
  },
  {
    id: 3,
    title: 'Enviar propuesta comercial',
    type: 'task',
    status: 'in_progress',
    due_date: '2024-06-16T17:00:00',
    contact_name: 'Roberto Silva',
    account_name: 'Nueva Empresa SAC',
    assigned_to: 'Juan Pérez',
    description: 'Preparar y enviar propuesta personalizada',
    priority: 'high'
  },
  {
    id: 4,
    title: 'Email de bienvenida',
    type: 'email',
    status: 'completed',
    due_date: '2024-06-13T09:00:00',
    contact_name: 'María López',
    account_name: 'Startup Innovadora',
    assigned_to: 'María García',
    description: 'Email de bienvenida con información del producto',
    priority: 'low'
  },
  {
    id: 5,
    title: 'Nota: Intereses del cliente',
    type: 'note',
    status: 'completed',
    due_date: '2024-06-12T16:00:00',
    contact_name: 'Luis Rodríguez',
    account_name: 'Tech Solutions SAC',
    assigned_to: 'Carlos Mendoza',
    description: 'Cliente interesado en módulo de inventarios',
    priority: 'medium'
  }
];

function ActivitiesList() {

  const [activities, setActivities] = useState(demoActivities);
  const [loading, setLoading] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedTab, setSelectedTab] = useState(0);
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(10);
  const [anchorEl, setAnchorEl] = useState(null);
  const [selectedActivity, setSelectedActivity] = useState(null);
  const [openDialog, setOpenDialog] = useState(false);
  const [dialogType, setDialogType] = useState('');

  const [activityForm, setActivityForm] = useState({
    title: '',
    type: 'call',
    status: 'pending',
    due_date: '',
    contact_name: '',
    account_name: '',
    description: '',
    priority: 'medium'
  });

  useEffect(() => {
    loadActivities();
  }, [selectedTab, searchTerm]);

  const loadActivities = () => {
    setLoading(true);
    setTimeout(() => {
      let filteredActivities = [...demoActivities];
      
      if (searchTerm) {
        filteredActivities = filteredActivities.filter(activity =>
          activity.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
          activity.contact_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
          activity.account_name.toLowerCase().includes(searchTerm.toLowerCase())
        );
      }

      // Filtrar por tab
      if (selectedTab === 1) { // Pendientes
        filteredActivities = filteredActivities.filter(a => a.status === 'pending');
      } else if (selectedTab === 2) { // Hoy
        const today = new Date().toISOString().split('T')[0];
        filteredActivities = filteredActivities.filter(a => a.due_date.startsWith(today));
      } else if (selectedTab === 3) { // Completadas
        filteredActivities = filteredActivities.filter(a => a.status === 'completed');
      }

      setActivities(filteredActivities);
      setLoading(false);
    }, 500);
  };

  const handleMenuClick = (event, activity) => {
    setAnchorEl(event.currentTarget);
    setSelectedActivity(activity);
  };

  const handleMenuClose = () => {
    setAnchorEl(null);
    setSelectedActivity(null);
  };

  const handleDialogOpen = (type, activity = null) => {
    setDialogType(type);
    setSelectedActivity(activity);
    
    if (activity && type === 'edit') {
      setActivityForm({
        title: activity.title,
        type: activity.type,
        status: activity.status,
        due_date: activity.due_date.slice(0, 16),
        contact_name: activity.contact_name,
        account_name: activity.account_name,
        description: activity.description,
        priority: activity.priority
      });
    } else if (type === 'create') {
      setActivityForm({
        title: '',
        type: 'call',
        status: 'pending',
        due_date: '',
        contact_name: '',
        account_name: '',
        description: '',
        priority: 'medium'
      });
    }
    
    setOpenDialog(true);
    handleMenuClose();
  };

  const handleDialogClose = () => {
    setOpenDialog(false);
    setSelectedActivity(null);
    setDialogType('');
    setActivityForm({
      title: '',
      type: 'call',
      status: 'pending',
      due_date: '',
      contact_name: '',
      account_name: '',
      description: '',
      priority: 'medium'
    });
  };

  const handleSaveActivity = () => {
    if (dialogType === 'create') {
      const newActivity = {
        ...activityForm,
        id: Date.now(),
        assigned_to: 'Usuario Actual'
      };
      demoActivities.unshift(newActivity);
    } else if (dialogType === 'edit') {
      const index = demoActivities.findIndex(a => a.id === selectedActivity.id);
      if (index !== -1) {
        demoActivities[index] = { ...demoActivities[index], ...activityForm };
      }
    }
    
    loadActivities();
    handleDialogClose();
  };

  const handleMarkComplete = () => {
    if (selectedActivity) {
      const index = demoActivities.findIndex(a => a.id === selectedActivity.id);
      if (index !== -1) {
        demoActivities[index].status = 'completed';
      }
      loadActivities();
    }
    handleMenuClose();
  };

  const handleDelete = () => {
    if (selectedActivity) {
      const index = demoActivities.findIndex(a => a.id === selectedActivity.id);
      if (index !== -1) {
        demoActivities.splice(index, 1);
      }
      loadActivities();
    }
    handleDialogClose();
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const isOverdue = (dateString, status) => {
    if (status === 'completed') return false;
    return new Date(dateString) < new Date();
  };

  const getActivityStats = () => {
    const total = demoActivities.length;
    const pending = demoActivities.filter(a => a.status === 'pending').length;
    const completed = demoActivities.filter(a => a.status === 'completed').length;
    const overdue = demoActivities.filter(a => isOverdue(a.due_date, a.status)).length;
    
    return { total, pending, completed, overdue };
  };

  const stats = getActivityStats();

  return (
    <Box>
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
        <Typography variant="h4" fontWeight="bold">
          Actividades
        </Typography>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={() => handleDialogOpen('create')}
        >
          Nueva Actividad
        </Button>
      </Box>

      {/* Stats Cards */}
      <Grid container spacing={3} mb={3}>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Box display="flex" alignItems="center">
                <Avatar sx={{ bgcolor: 'primary.main', mr: 2 }}>
                  <TaskIcon />
                </Avatar>
                <Box>
                  <Typography variant="h4" fontWeight="bold">
                    {stats.total}
                  </Typography>
                  <Typography variant="body2" color="textSecondary">
                    Total Actividades
                  </Typography>
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>
        
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Box display="flex" alignItems="center">
                <Avatar sx={{ bgcolor: 'warning.main', mr: 2 }}>
                  <ScheduleIcon />
                </Avatar>
                <Box>
                  <Typography variant="h4" fontWeight="bold">
                    {stats.pending}
                  </Typography>
                  <Typography variant="body2" color="textSecondary">
                    Pendientes
                  </Typography>
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>
        
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Box display="flex" alignItems="center">
                <Avatar sx={{ bgcolor: 'success.main', mr: 2 }}>
                  <CheckCircleIcon />
                </Avatar>
                <Box>
                  <Typography variant="h4" fontWeight="bold">
                    {stats.completed}
                  </Typography>
                  <Typography variant="body2" color="textSecondary">
                    Completadas
                  </Typography>
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>
        
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Box display="flex" alignItems="center">
                <Avatar sx={{ bgcolor: 'error.main', mr: 2 }}>
                  <ScheduleIcon />
                </Avatar>
                <Box>
                  <Typography variant="h4" fontWeight="bold">
                    {stats.overdue}
                  </Typography>
                  <Typography variant="body2" color="textSecondary">
                    Vencidas
                  </Typography>
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      <Paper sx={{ mb: 3 }}>
        <Tabs
          value={selectedTab}
          onChange={(e, newValue) => setSelectedTab(newValue)}
          sx={{ borderBottom: 1, borderColor: 'divider' }}
        >
          <Tab label="Todas" />
          <Tab label="Pendientes" />
          <Tab label="Hoy" />
          <Tab label="Completadas" />
        </Tabs>
        
        <Box p={2}>
          <TextField
            fullWidth
            placeholder="Buscar actividades..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <SearchIcon />
                </InputAdornment>
              ),
            }}
          />
        </Box>
      </Paper>

      {loading ? (
        <Box display="flex" justifyContent="center" p={4}>
          <Typography>Cargando actividades...</Typography>
        </Box>
      ) : (
        <>
          <TableContainer component={Paper}>
            <Table>
              <TableHead>
                <TableRow>
                  <TableCell>Actividad</TableCell>
                  <TableCell>Tipo</TableCell>
                  <TableCell>Estado</TableCell>
                  <TableCell>Contacto</TableCell>
                  <TableCell>Fecha</TableCell>
                  <TableCell>Asignado</TableCell>
                  <TableCell align="right">Acciones</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {activities.slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage).map((activity) => (
                  <TableRow key={activity.id} hover>
                    <TableCell>
                      <Box>
                        <Typography variant="subtitle2" fontWeight="bold">
                          {activity.title}
                        </Typography>
                        <Typography variant="caption" color="textSecondary">
                          {activity.description}
                        </Typography>
                        {isOverdue(activity.due_date, activity.status) && (
                          <Chip
                            label="Vencida"
                            size="small"
                            color="error"
                            sx={{ ml: 1, mt: 0.5 }}
                          />
                        )}
                      </Box>
                    </TableCell>
                    <TableCell>
                      <Chip
                        icon={activityTypes[activity.type].icon}
                        label={activityTypes[activity.type].label}
                        size="small"
                        color={activityTypes[activity.type].color}
                        variant="outlined"
                      />
                    </TableCell>
                    <TableCell>
                      <Chip
                        label={activityStatuses[activity.status].label}
                        size="small"
                        color={activityStatuses[activity.status].color}
                      />
                    </TableCell>
                    <TableCell>
                      <Box>
                        <Typography variant="body2">
                          {activity.contact_name}
                        </Typography>
                        <Typography variant="caption" color="textSecondary">
                          {activity.account_name}
                        </Typography>
                      </Box>
                    </TableCell>
                    <TableCell>
                      <Typography variant="body2">
                        {formatDate(activity.due_date)}
                      </Typography>
                    </TableCell>
                    <TableCell>
                      <Box display="flex" alignItems="center">
                        <Avatar sx={{ width: 24, height: 24, mr: 1 }}>
                          <PersonIcon fontSize="small" />
                        </Avatar>
                        <Typography variant="body2">
                          {activity.assigned_to}
                        </Typography>
                      </Box>
                    </TableCell>
                    <TableCell align="right">
                      <IconButton
                        size="small"
                        onClick={(e) => handleMenuClick(e, activity)}
                      >
                        <MoreVertIcon />
                      </IconButton>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </TableContainer>

          <TablePagination
            component="div"
            count={activities.length}
            page={page}
            onPageChange={(e, newPage) => setPage(newPage)}
            rowsPerPage={rowsPerPage}
            onRowsPerPageChange={(e) => setRowsPerPage(parseInt(e.target.value, 10))}
            labelRowsPerPage="Filas por página:"
          />
        </>
      )}

      {/* Menu contextual */}
      <Menu
        anchorEl={anchorEl}
        open={Boolean(anchorEl)}
        onClose={handleMenuClose}
      >
        <MenuItem onClick={() => handleDialogOpen('view')}>
          <ViewIcon sx={{ mr: 1 }} />
          Ver detalles
        </MenuItem>
        <MenuItem onClick={() => handleDialogOpen('edit')}>
          <EditIcon sx={{ mr: 1 }} />
          Editar
        </MenuItem>
        {selectedActivity?.status !== 'completed' && (
          <MenuItem onClick={handleMarkComplete}>
            <CheckCircleIcon sx={{ mr: 1 }} />
            Marcar completada
          </MenuItem>
        )}
        <MenuItem onClick={() => handleDialogOpen('delete')}>
          <DeleteIcon sx={{ mr: 1 }} />
          Eliminar
        </MenuItem>
      </Menu>

      {/* Diálogo de formulario */}
      <Dialog open={openDialog && (dialogType === 'create' || dialogType === 'edit')} onClose={handleDialogClose} maxWidth="md" fullWidth>
        <DialogTitle>
          {dialogType === 'create' ? 'Nueva Actividad' : 'Editar Actividad'}
        </DialogTitle>
        <DialogContent>
          <Grid container spacing={2} sx={{ mt: 1 }}>
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Título *"
                value={activityForm.title}
                onChange={(e) => setActivityForm({...activityForm, title: e.target.value})}
              />
            </Grid>
            <Grid item xs={12} sm={6}>
              <FormControl fullWidth>
                <InputLabel>Tipo</InputLabel>
                <Select
                  value={activityForm.type}
                  onChange={(e) => setActivityForm({...activityForm, type: e.target.value})}
                  label="Tipo"
                >
                  {Object.entries(activityTypes).map(([key, type]) => (
                    <MenuItem key={key} value={key}>
                      {type.label}
                    </MenuItem>
                  ))}
                </Select>
              </FormControl>
            </Grid>
            <Grid item xs={12} sm={6}>
              <FormControl fullWidth>
                <InputLabel>Estado</InputLabel>
                <Select
                  value={activityForm.status}
                  onChange={(e) => setActivityForm({...activityForm, status: e.target.value})}
                  label="Estado"
                >
                  {Object.entries(activityStatuses).map(([key, status]) => (
                    <MenuItem key={key} value={key}>
                      {status.label}
                    </MenuItem>
                  ))}
                </Select>
              </FormControl>
            </Grid>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Contacto"
                value={activityForm.contact_name}
                onChange={(e) => setActivityForm({...activityForm, contact_name: e.target.value})}
              />
            </Grid>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Cuenta"
                value={activityForm.account_name}
                onChange={(e) => setActivityForm({...activityForm, account_name: e.target.value})}
              />
            </Grid>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Fecha y Hora"
                type="datetime-local"
                value={activityForm.due_date}
                onChange={(e) => setActivityForm({...activityForm, due_date: e.target.value})}
                InputLabelProps={{ shrink: true }}
              />
            </Grid>
            <Grid item xs={12} sm={6}>
              <FormControl fullWidth>
                <InputLabel>Prioridad</InputLabel>
                <Select
                  value={activityForm.priority}
                  onChange={(e) => setActivityForm({...activityForm, priority: e.target.value})}
                  label="Prioridad"
                >
                  <MenuItem value="low">Baja</MenuItem>
                  <MenuItem value="medium">Media</MenuItem>
                  <MenuItem value="high">Alta</MenuItem>
                </Select>
              </FormControl>
            </Grid>
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Descripción"
                value={activityForm.description}
                onChange={(e) => setActivityForm({...activityForm, description: e.target.value})}
                multiline
                rows={3}
              />
            </Grid>
          </Grid>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleDialogClose}>Cancelar</Button>
          <Button onClick={handleSaveActivity} variant="contained">
            {dialogType === 'create' ? 'Crear' : 'Actualizar'}
          </Button>
        </DialogActions>
      </Dialog>

      {/* Diálogo de confirmación de eliminación */}
      <Dialog open={openDialog && dialogType === 'delete'} onClose={handleDialogClose}>
        <DialogTitle>Confirmar eliminación</DialogTitle>
        <DialogContent>
          <Typography>
            ¿Estás seguro de que deseas eliminar esta actividad?
            Esta acción no se puede deshacer.
          </Typography>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleDialogClose}>Cancelar</Button>
          <Button onClick={handleDelete} color="error" variant="contained">
            Eliminar
          </Button>
        </DialogActions>
      </Dialog>

      {/* FAB para móvil */}
      <Fab
        color="primary"
        sx={{
          position: 'fixed',
          bottom: 16,
          right: 16,
          display: { xs: 'flex', md: 'none' }
        }}
        onClick={() => handleDialogOpen('create')}
      >
        <AddIcon />
      </Fab>
    </Box>
  );
}

export default ActivitiesList;