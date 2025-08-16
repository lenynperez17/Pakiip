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
  Rating,
  Badge,
  LinearProgress
} from '@mui/material';
import {
  Add as AddIcon,
  Search as SearchIcon,
  Support as SupportIcon,
  MoreVert as MoreVertIcon,
  Assignment as AssignmentIcon,
  Schedule as ScheduleIcon,
  Person as PersonIcon,
  Business as BusinessIcon,
  Edit as EditIcon,
  Delete as DeleteIcon,
  Visibility as ViewIcon,
  CheckCircle as CheckCircleIcon,
  Warning as WarningIcon,
  Error as ErrorIcon,
  Comment as CommentIcon
} from '@mui/icons-material';
import { ticketsAPI } from '../../services/api';
import TicketForm from '../../components/Forms/TicketForm';

const priorityColors = {
  'low': '#4CAF50',
  'medium': '#FF9800',
  'high': '#F44336',
  'urgent': '#9C27B0'
};

const priorityLabels = {
  'low': 'Baja',
  'medium': 'Media',
  'high': 'Alta',
  'urgent': 'Urgente'
};

const statusColors = {
  'new': '#2196F3',
  'open': '#FF9800',
  'pending': '#9C27B0',
  'on_hold': '#607D8B',
  'resolved': '#4CAF50',
  'closed': '#757575',
  'cancelled': '#F44336'
};

const statusLabels = {
  'new': 'Nuevo',
  'open': 'Abierto',
  'pending': 'Pendiente',
  'on_hold': 'En Espera',
  'resolved': 'Resuelto',
  'closed': 'Cerrado',
  'cancelled': 'Cancelado'
};

const typeLabels = {
  'question': 'Pregunta',
  'problem': 'Problema',
  'feature_request': 'Solicitud',
  'bug': 'Error',
  'complaint': 'Queja',
  'other': 'Otro'
};

function TicketsList() {
  const [tickets, setTickets] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(10);
  const [anchorEl, setAnchorEl] = useState(null);
  const [selectedTicket, setSelectedTicket] = useState(null);
  const [openDialog, setOpenDialog] = useState(false);
  const [dialogType, setDialogType] = useState('');
  const [ticketStats, setTicketStats] = useState({});

  useEffect(() => {
    loadTickets();
    loadTicketStats();
  }, [searchTerm, page, rowsPerPage]);

  const loadTickets = async () => {
    try {
      setLoading(true);
      const params = {
        search: searchTerm,
        page: page + 1,
        page_size: rowsPerPage
      };
      const response = await ticketsAPI.getTickets(params);
      setTickets(response.data.results || response.data);
    } catch (error) {
      console.error('Error loading tickets:', error);
    } finally {
      setLoading(false);
    }
  };

  const loadTicketStats = async () => {
    try {
      const response = await ticketsAPI.getTicketStatistics();
      setTicketStats(response.data);
    } catch (error) {
      console.error('Error loading ticket stats:', error);
    }
  };

  const handleMenuClick = (event, ticket) => {
    setAnchorEl(event.currentTarget);
    setSelectedTicket(ticket);
  };

  const handleMenuClose = () => {
    setAnchorEl(null);
    setSelectedTicket(null);
  };

  const handleDialogOpen = (type, ticket = null) => {
    setDialogType(type);
    setSelectedTicket(ticket);
    setOpenDialog(true);
    handleMenuClose();
  };

  const handleDialogClose = () => {
    setOpenDialog(false);
    setSelectedTicket(null);
    setDialogType('');
  };

  const handleDelete = async () => {
    try {
      await ticketsAPI.deleteTicket(selectedTicket.id);
      loadTickets();
      handleDialogClose();
    } catch (error) {
      console.error('Error deleting ticket:', error);
    }
  };

  const handleResolve = async () => {
    try {
      await ticketsAPI.resolveTicket(selectedTicket.id);
      loadTickets();
      handleDialogClose();
    } catch (error) {
      console.error('Error resolving ticket:', error);
    }
  };

  const getSlaStatus = (ticket) => {
    if (!ticket.sla_due_date) return null;
    
    const now = new Date();
    const dueDate = new Date(ticket.sla_due_date);
    const diffTime = dueDate - now;
    const diffHours = Math.ceil(diffTime / (1000 * 60 * 60));
    
    if (ticket.status === 'resolved' || ticket.status === 'closed') {
      return { color: 'success', text: 'Cumplido', icon: <CheckCircleIcon /> };
    }
    
    if (diffHours < 0) {
      return { color: 'error', text: `${Math.abs(diffHours)}h vencido`, icon: <ErrorIcon /> };
    }
    
    if (diffHours <= 2) {
      return { color: 'warning', text: `${diffHours}h restantes`, icon: <WarningIcon /> };
    }
    
    return { color: 'info', text: `${diffHours}h restantes`, icon: <ScheduleIcon /> };
  };

  const StatsCard = ({ title, value, icon, color }) => (
    <Card>
      <CardContent>
        <Box display="flex" alignItems="center">
          <Avatar sx={{ bgcolor: color, mr: 2 }}>
            {icon}
          </Avatar>
          <Box>
            <Typography variant="h4" fontWeight="bold">
              {value}
            </Typography>
            <Typography variant="body2" color="textSecondary">
              {title}
            </Typography>
          </Box>
        </Box>
      </CardContent>
    </Card>
  );

  return (
    <Box>
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
        <Typography variant="h4" fontWeight="bold">
          Tickets de Soporte
        </Typography>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={() => handleDialogOpen('create')}
        >
          Nuevo Ticket
        </Button>
      </Box>

      {/* Stats Cards */}
      <Grid container spacing={3} mb={3}>
        <Grid item xs={12} sm={6} md={3}>
          <StatsCard
            title="Tickets Abiertos"
            value={ticketStats.open_tickets || 0}
            icon={<AssignmentIcon />}
            color="primary.main"
          />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <StatsCard
            title="Tickets Vencidos"
            value={ticketStats.overdue_tickets || 0}
            icon={<ErrorIcon />}
            color="error.main"
          />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <StatsCard
            title="Satisfacción"
            value={ticketStats.avg_satisfaction || 0}
            icon={<CheckCircleIcon />}
            color="success.main"
          />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <StatsCard
            title="Tiempo Respuesta"
            value={`${ticketStats.avg_response_time || 0}h`}
            icon={<ScheduleIcon />}
            color="info.main"
          />
        </Grid>
      </Grid>

      <Paper sx={{ mb: 3 }}>
        <Box p={2}>
          <TextField
            fullWidth
            placeholder="Buscar tickets..."
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
          <Typography>Cargando tickets...</Typography>
        </Box>
      ) : (
        <>
          <TableContainer component={Paper}>
            <Table>
              <TableHead>
                <TableRow>
                  <TableCell>Ticket</TableCell>
                  <TableCell>Cliente</TableCell>
                  <TableCell>Tipo</TableCell>
                  <TableCell>Prioridad</TableCell>
                  <TableCell>Estado</TableCell>
                  <TableCell>SLA</TableCell>
                  <TableCell>Satisfacción</TableCell>
                  <TableCell align="right">Acciones</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {tickets.map((ticket) => {
                  const slaStatus = getSlaStatus(ticket);
                  return (
                    <TableRow key={ticket.id} hover>
                      <TableCell>
                        <Box>
                          <Typography variant="subtitle2" fontWeight="bold">
                            {ticket.subject}
                          </Typography>
                          <Typography variant="caption" color="textSecondary">
                            #{ticket.ticket_number}
                          </Typography>
                        </Box>
                      </TableCell>
                      <TableCell>
                        <Box display="flex" alignItems="center">
                          <Avatar sx={{ width: 24, height: 24, mr: 1 }}>
                            <PersonIcon fontSize="small" />
                          </Avatar>
                          <Box>
                            <Typography variant="body2">
                              {ticket.contact_name}
                            </Typography>
                            {ticket.account_name && (
                              <Typography variant="caption" color="textSecondary">
                                {ticket.account_name}
                              </Typography>
                            )}
                          </Box>
                        </Box>
                      </TableCell>
                      <TableCell>
                        <Chip
                          label={typeLabels[ticket.ticket_type]}
                          size="small"
                          variant="outlined"
                        />
                      </TableCell>
                      <TableCell>
                        <Chip
                          label={priorityLabels[ticket.priority]}
                          size="small"
                          sx={{
                            backgroundColor: priorityColors[ticket.priority],
                            color: 'white'
                          }}
                        />
                      </TableCell>
                      <TableCell>
                        <Chip
                          label={statusLabels[ticket.status]}
                          size="small"
                          sx={{
                            backgroundColor: statusColors[ticket.status],
                            color: 'white'
                          }}
                        />
                      </TableCell>
                      <TableCell>
                        {slaStatus && (
                          <Chip
                            label={slaStatus.text}
                            size="small"
                            color={slaStatus.color}
                            icon={slaStatus.icon}
                            variant="outlined"
                          />
                        )}
                      </TableCell>
                      <TableCell>
                        {ticket.satisfaction_rating ? (
                          <Rating
                            value={ticket.satisfaction_rating}
                            size="small"
                            readOnly
                          />
                        ) : (
                          <Typography variant="caption" color="textSecondary">
                            Sin calificar
                          </Typography>
                        )}
                      </TableCell>
                      <TableCell align="right">
                        <IconButton
                          size="small"
                          onClick={(e) => handleMenuClick(e, ticket)}
                        >
                          <Badge
                            badgeContent={ticket.comments_count || 0}
                            color="primary"
                            max={99}
                          >
                            <MoreVertIcon />
                          </Badge>
                        </IconButton>
                      </TableCell>
                    </TableRow>
                  );
                })}
              </TableBody>
            </Table>
          </TableContainer>

          <TablePagination
            component="div"
            count={-1}
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
        <MenuItem onClick={() => handleDialogOpen('comment')}>
          <CommentIcon sx={{ mr: 1 }} />
          Agregar comentario
        </MenuItem>
        {selectedTicket?.status !== 'resolved' && selectedTicket?.status !== 'closed' && (
          <MenuItem onClick={() => handleDialogOpen('resolve')}>
            <CheckCircleIcon sx={{ mr: 1 }} />
            Resolver ticket
          </MenuItem>
        )}
        <MenuItem onClick={() => handleDialogOpen('edit')}>
          <EditIcon sx={{ mr: 1 }} />
          Editar
        </MenuItem>
        <MenuItem onClick={() => handleDialogOpen('delete')}>
          <DeleteIcon sx={{ mr: 1 }} />
          Eliminar
        </MenuItem>
      </Menu>

      {/* Ticket Form Dialog */}
      <TicketForm
        open={openDialog && (dialogType === 'create' || dialogType === 'edit')}
        onClose={handleDialogClose}
        ticket={dialogType === 'edit' ? selectedTicket : null}
        onSave={loadTickets}
      />

      {/* Diálogo de resolución */}
      <Dialog open={openDialog && dialogType === 'resolve'} onClose={handleDialogClose}>
        <DialogTitle>Resolver Ticket</DialogTitle>
        <DialogContent>
          <Typography>
            ¿Estás seguro de que deseas marcar este ticket como resuelto?
          </Typography>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleDialogClose}>Cancelar</Button>
          <Button onClick={handleResolve} color="success" variant="contained">
            Resolver
          </Button>
        </DialogActions>
      </Dialog>

      {/* Diálogo de confirmación de eliminación */}
      <Dialog open={openDialog && dialogType === 'delete'} onClose={handleDialogClose}>
        <DialogTitle>Confirmar eliminación</DialogTitle>
        <DialogContent>
          <Typography>
            ¿Estás seguro de que deseas eliminar este ticket?
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

export default TicketsList;