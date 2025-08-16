import React, { useState, useEffect } from 'react';
import {
  Box,
  Paper,
  Typography,
  Button,
  IconButton,
  Chip,
  LinearProgress,
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
  Avatar,
  Badge
} from '@mui/material';
import {
  Add as AddIcon,
  Search as SearchIcon,
  TrendingUp as TrendingUpIcon,
  MoreVert as MoreVertIcon,
  AttachMoney as MoneyIcon,
  Schedule as ScheduleIcon,
  Business as BusinessIcon,
  Person as PersonIcon,
  Edit as EditIcon,
  Delete as DeleteIcon,
  Visibility as ViewIcon,
  Assessment as AssessmentIcon
} from '@mui/icons-material';
import { opportunitiesAPI } from '../../services/api';
import OpportunityForm from '../../components/Forms/OpportunityForm';

const stageColors = {
  'qualification': '#2196F3',
  'needs_analysis': '#FF9800',
  'proposal': '#9C27B0',
  'negotiation': '#F44336',
  'closed_won': '#4CAF50',
  'closed_lost': '#757575'
};

const stageLabels = {
  'qualification': 'Calificación',
  'needs_analysis': 'Análisis',
  'proposal': 'Propuesta',
  'negotiation': 'Negociación',
  'closed_won': 'Ganado',
  'closed_lost': 'Perdido'
};

function OpportunitiesList() {
  const [opportunities, setOpportunities] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(10);
  const [anchorEl, setAnchorEl] = useState(null);
  const [selectedOpportunity, setSelectedOpportunity] = useState(null);
  const [openDialog, setOpenDialog] = useState(false);
  const [dialogType, setDialogType] = useState('');
  const [pipelineStats, setPipelineStats] = useState({});

  useEffect(() => {
    loadOpportunities();
    loadPipelineStats();
  }, [searchTerm, page, rowsPerPage]);

  const loadOpportunities = async () => {
    try {
      setLoading(true);
      const params = {
        search: searchTerm,
        page: page + 1,
        page_size: rowsPerPage
      };
      const response = await opportunitiesAPI.getOpportunities(params);
      setOpportunities(response.data.results || response.data);
    } catch (error) {
      console.error('Error loading opportunities:', error);
    } finally {
      setLoading(false);
    }
  };

  const loadPipelineStats = async () => {
    try {
      const response = await opportunitiesAPI.getPipelineAnalysis();
      setPipelineStats(response.data);
    } catch (error) {
      console.error('Error loading pipeline stats:', error);
    }
  };

  const handleMenuClick = (event, opportunity) => {
    setAnchorEl(event.currentTarget);
    setSelectedOpportunity(opportunity);
  };

  const handleMenuClose = () => {
    setAnchorEl(null);
    setSelectedOpportunity(null);
  };

  const handleDialogOpen = (type, opportunity = null) => {
    setDialogType(type);
    setSelectedOpportunity(opportunity);
    setOpenDialog(true);
    handleMenuClose();
  };

  const handleDialogClose = () => {
    setOpenDialog(false);
    setSelectedOpportunity(null);
    setDialogType('');
  };

  const handleDelete = async () => {
    try {
      await opportunitiesAPI.deleteOpportunity(selectedOpportunity.id);
      loadOpportunities();
      handleDialogClose();
    } catch (error) {
      console.error('Error deleting opportunity:', error);
    }
  };

  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('es-PE', {
      style: 'currency',
      currency: 'PEN'
    }).format(amount);
  };

  const getDaysUntilClose = (closeDate) => {
    const today = new Date();
    const close = new Date(closeDate);
    const diffTime = close - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
  };

  const getCloseAlert = (closeDate, stage) => {
    const days = getDaysUntilClose(closeDate);
    if (stage === 'closed_won' || stage === 'closed_lost') return null;
    
    if (days < 0) return { color: 'error', text: `${Math.abs(days)} días vencido` };
    if (days <= 7) return { color: 'warning', text: `${days} días restantes` };
    return { color: 'info', text: `${days} días restantes` };
  };

  const PipelineCard = ({ stage, count, value }) => (
    <Card sx={{ minWidth: 200, mx: 1 }}>
      <CardContent>
        <Box display="flex" alignItems="center" justifyContent="space-between">
          <Box>
            <Typography variant="h6" color={stageColors[stage]}>
              {count}
            </Typography>
            <Typography variant="body2" color="textSecondary">
              {stageLabels[stage]}
            </Typography>
          </Box>
          <Box>
            <Typography variant="h6" textAlign="right">
              {formatCurrency(value)}
            </Typography>
          </Box>
        </Box>
        <Box mt={1}>
          <Chip 
            label={stageLabels[stage]}
            size="small"
            sx={{ 
              backgroundColor: stageColors[stage], 
              color: 'white' 
            }}
          />
        </Box>
      </CardContent>
    </Card>
  );

  return (
    <Box>
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
        <Typography variant="h4" fontWeight="bold">
          Oportunidades
        </Typography>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={() => handleDialogOpen('create')}
        >
          Nueva Oportunidad
        </Button>
      </Box>

      {/* Pipeline Overview */}
      <Paper sx={{ p: 2, mb: 3 }}>
        <Typography variant="h6" mb={2} display="flex" alignItems="center">
          <AssessmentIcon sx={{ mr: 1 }} />
          Pipeline de Ventas
        </Typography>
        <Box display="flex" sx={{ overflowX: 'auto' }}>
          {Object.entries(pipelineStats).map(([stage, data]) => (
            <PipelineCard 
              key={stage}
              stage={stage}
              count={data.count || 0}
              value={data.value || 0}
            />
          ))}
        </Box>
      </Paper>

      <Paper sx={{ mb: 3 }}>
        <Box p={2}>
          <TextField
            fullWidth
            placeholder="Buscar oportunidades..."
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
          <Typography>Cargando oportunidades...</Typography>
        </Box>
      ) : (
        <>
          <TableContainer component={Paper}>
            <Table>
              <TableHead>
                <TableRow>
                  <TableCell>Oportunidad</TableCell>
                  <TableCell>Cuenta</TableCell>
                  <TableCell>Etapa</TableCell>
                  <TableCell>Valor</TableCell>
                  <TableCell>Probabilidad</TableCell>
                  <TableCell>Cierre</TableCell>
                  <TableCell align="right">Acciones</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {opportunities.map((opportunity) => {
                  const closeAlert = getCloseAlert(opportunity.close_date, opportunity.stage);
                  return (
                    <TableRow key={opportunity.id} hover>
                      <TableCell>
                        <Box>
                          <Typography variant="subtitle2" fontWeight="bold">
                            {opportunity.name}
                          </Typography>
                          <Typography variant="caption" color="textSecondary">
                            {opportunity.opportunity_id}
                          </Typography>
                        </Box>
                      </TableCell>
                      <TableCell>
                        <Box display="flex" alignItems="center">
                          <Avatar sx={{ width: 24, height: 24, mr: 1 }}>
                            <BusinessIcon fontSize="small" />
                          </Avatar>
                          {opportunity.account_name}
                        </Box>
                      </TableCell>
                      <TableCell>
                        <Chip
                          label={stageLabels[opportunity.stage]}
                          size="small"
                          sx={{
                            backgroundColor: stageColors[opportunity.stage],
                            color: 'white'
                          }}
                        />
                      </TableCell>
                      <TableCell>
                        <Typography variant="subtitle2" fontWeight="bold" color="success.main">
                          {formatCurrency(opportunity.amount)}
                        </Typography>
                      </TableCell>
                      <TableCell>
                        <Box display="flex" alignItems="center" gap={1}>
                          <LinearProgress
                            variant="determinate"
                            value={opportunity.probability}
                            sx={{ width: 60, height: 6, borderRadius: 3 }}
                          />
                          <Typography variant="caption">
                            {opportunity.probability}%
                          </Typography>
                        </Box>
                      </TableCell>
                      <TableCell>
                        <Box>
                          <Typography variant="body2">
                            {new Date(opportunity.close_date).toLocaleDateString('es-ES')}
                          </Typography>
                          {closeAlert && (
                            <Chip
                              label={closeAlert.text}
                              size="small"
                              color={closeAlert.color}
                              variant="outlined"
                              sx={{ mt: 0.5 }}
                            />
                          )}
                        </Box>
                      </TableCell>
                      <TableCell align="right">
                        <IconButton
                          size="small"
                          onClick={(e) => handleMenuClick(e, opportunity)}
                        >
                          <MoreVertIcon />
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
        <MenuItem onClick={() => handleDialogOpen('edit')}>
          <EditIcon sx={{ mr: 1 }} />
          Editar
        </MenuItem>
        <MenuItem onClick={() => handleDialogOpen('products')}>
          <MoneyIcon sx={{ mr: 1 }} />
          Productos
        </MenuItem>
        <MenuItem onClick={() => handleDialogOpen('quote')}>
          <AssessmentIcon sx={{ mr: 1 }} />
          Generar cotización
        </MenuItem>
        <MenuItem onClick={() => handleDialogOpen('delete')}>
          <DeleteIcon sx={{ mr: 1 }} />
          Eliminar
        </MenuItem>
      </Menu>

      {/* Opportunity Form Dialog */}
      <OpportunityForm
        open={openDialog && (dialogType === 'create' || dialogType === 'edit')}
        onClose={handleDialogClose}
        opportunity={dialogType === 'edit' ? selectedOpportunity : null}
        onSave={loadOpportunities}
      />

      {/* Diálogo de confirmación de eliminación */}
      <Dialog open={openDialog && dialogType === 'delete'} onClose={handleDialogClose}>
        <DialogTitle>Confirmar eliminación</DialogTitle>
        <DialogContent>
          <Typography>
            ¿Estás seguro de que deseas eliminar esta oportunidad?
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

export default OpportunitiesList;