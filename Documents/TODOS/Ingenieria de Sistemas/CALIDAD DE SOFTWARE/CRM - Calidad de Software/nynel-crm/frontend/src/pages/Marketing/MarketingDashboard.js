import React, { useState, useEffect } from 'react';
import {
  Box,
  Paper,
  Typography,
  Button,
  Grid,
  Card,
  CardContent,
  CardActions,
  IconButton,
  Chip,
  Avatar,
  LinearProgress,
  Tabs,
  Tab,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Menu,
  MenuItem,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions
} from '@mui/material';
import {
  Add as AddIcon,
  Campaign as CampaignIcon,
  People as PeopleIcon,
  Email as EmailIcon,
  TrendingUp as TrendingUpIcon,
  MoreVert as MoreVertIcon,
  Launch as LaunchIcon,
  Pause as PauseIcon,
  Stop as StopIcon,
  Edit as EditIcon,
  Delete as DeleteIcon,
  Assessment as AssessmentIcon,
  Star as StarIcon,
  PersonAdd as PersonAddIcon
} from '@mui/icons-material';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, PieChart, Pie, Cell, ResponsiveContainer } from 'recharts';
import { marketingAPI } from '../../services/api';
import CampaignForm from '../../components/Forms/CampaignForm';

const campaignStatusColors = {
  'planning': '#FF9800',
  'active': '#4CAF50',
  'paused': '#2196F3',
  'completed': '#9C27B0',
  'cancelled': '#F44336'
};

const campaignStatusLabels = {
  'planning': 'Planificación',
  'active': 'Activo',
  'paused': 'Pausado',
  'completed': 'Completado',
  'cancelled': 'Cancelado'
};

const leadStatusColors = {
  'new': '#2196F3',
  'contacted': '#FF9800',
  'qualified': '#4CAF50',
  'unqualified': '#F44336',
  'converted': '#9C27B0'
};

function MarketingDashboard() {
  const [campaigns, setCampaigns] = useState([]);
  const [leads, setLeads] = useState([]);
  const [emailTemplates, setEmailTemplates] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedTab, setSelectedTab] = useState(0);
  const [anchorEl, setAnchorEl] = useState(null);
  const [selectedItem, setSelectedItem] = useState(null);
  const [openDialog, setOpenDialog] = useState(false);
  const [dialogType, setDialogType] = useState('');
  const [marketingStats, setMarketingStats] = useState({});

  useEffect(() => {
    loadData();
  }, [selectedTab]);

  const loadData = async () => {
    try {
      setLoading(true);
      const [campaignsRes, leadsRes, templatesRes] = await Promise.all([
        marketingAPI.getCampaigns(),
        marketingAPI.getLeads(),
        marketingAPI.getEmailTemplates()
      ]);
      
      setCampaigns(campaignsRes.data.results || campaignsRes.data);
      setLeads(leadsRes.data.results || leadsRes.data);
      setEmailTemplates(templatesRes.data.results || templatesRes.data);
      
      // Calculate stats
      calculateStats(campaignsRes.data, leadsRes.data);
    } catch (error) {
      console.error('Error loading marketing data:', error);
    } finally {
      setLoading(false);
    }
  };

  const calculateStats = (campaignsData, leadsData) => {
    const stats = {
      totalCampaigns: campaignsData.length,
      activeCampaigns: campaignsData.filter(c => c.status === 'active').length,
      totalLeads: leadsData.length,
      convertedLeads: leadsData.filter(l => l.status === 'converted').length,
      conversionRate: leadsData.length > 0 ? (leadsData.filter(l => l.status === 'converted').length / leadsData.length * 100).toFixed(1) : 0
    };
    setMarketingStats(stats);
  };

  const handleMenuClick = (event, item) => {
    setAnchorEl(event.currentTarget);
    setSelectedItem(item);
  };

  const handleMenuClose = () => {
    setAnchorEl(null);
    setSelectedItem(null);
  };

  const handleDialogOpen = (type, item = null) => {
    setDialogType(type);
    setSelectedItem(item);
    setOpenDialog(true);
    handleMenuClose();
  };

  const handleDialogClose = () => {
    setOpenDialog(false);
    setSelectedItem(null);
    setDialogType('');
  };

  const handleDelete = async () => {
    try {
      if (selectedTab === 0) {
        await marketingAPI.deleteCampaign(selectedItem.id);
      } else if (selectedTab === 1) {
        await marketingAPI.deleteLead(selectedItem.id);
      } else {
        await marketingAPI.deleteEmailTemplate(selectedItem.id);
      }
      loadData();
      handleDialogClose();
    } catch (error) {
      console.error('Error deleting item:', error);
    }
  };

  const getScoreColor = (score) => {
    if (score >= 70) return 'success';
    if (score >= 40) return 'warning';
    return 'error';
  };

  const CampaignCard = ({ campaign }) => (
    <Card sx={{ height: '100%' }}>
      <CardContent>
        <Box display="flex" justifyContent="space-between" alignItems="flex-start" mb={2}>
          <Avatar sx={{ bgcolor: campaignStatusColors[campaign.status] }}>
            <CampaignIcon />
          </Avatar>
          <IconButton size="small" onClick={(e) => handleMenuClick(e, campaign)}>
            <MoreVertIcon />
          </IconButton>
        </Box>
        
        <Typography variant="h6" gutterBottom noWrap>
          {campaign.name}
        </Typography>
        
        <Typography variant="body2" color="textSecondary" gutterBottom>
          {campaign.description}
        </Typography>
        
        <Box display="flex" gap={1} mb={2}>
          <Chip
            label={campaignStatusLabels[campaign.status]}
            size="small"
            sx={{
              backgroundColor: campaignStatusColors[campaign.status],
              color: 'white'
            }}
          />
          <Chip
            label={campaign.campaign_type}
            size="small"
            variant="outlined"
          />
        </Box>
        
        <Box mb={2}>
          <Typography variant="body2" color="textSecondary">
            Leads: {campaign.actual_leads || 0} / {campaign.expected_leads || 0}
          </Typography>
          <LinearProgress
            variant="determinate"
            value={campaign.expected_leads > 0 ? (campaign.actual_leads / campaign.expected_leads * 100) : 0}
            sx={{ mt: 0.5 }}
          />
        </Box>
        
        <Typography variant="body2" color="textSecondary">
          Presupuesto: S/ {campaign.budget?.toLocaleString() || 0}
        </Typography>
      </CardContent>
      
      <CardActions>
        <Button size="small" startIcon={<AssessmentIcon />}>
          Ver métricas
        </Button>
        {campaign.status === 'planning' && (
          <Button size="small" startIcon={<LaunchIcon />} color="success">
            Activar
          </Button>
        )}
      </CardActions>
    </Card>
  );

  const LeadCard = ({ lead }) => (
    <Card sx={{ height: '100%' }}>
      <CardContent>
        <Box display="flex" justifyContent="space-between" alignItems="flex-start" mb={2}>
          <Avatar sx={{ bgcolor: 'primary.main' }}>
            <PersonAddIcon />
          </Avatar>
          <IconButton size="small" onClick={(e) => handleMenuClick(e, lead)}>
            <MoreVertIcon />
          </IconButton>
        </Box>
        
        <Typography variant="h6" gutterBottom>
          {lead.first_name} {lead.last_name}
        </Typography>
        
        <Typography variant="body2" color="textSecondary" gutterBottom>
          {lead.job_title} en {lead.company}
        </Typography>
        
        <Typography variant="body2" color="textSecondary" gutterBottom>
          {lead.email}
        </Typography>
        
        <Box display="flex" gap={1} mb={2}>
          <Chip
            label={lead.status}
            size="small"
            sx={{
              backgroundColor: leadStatusColors[lead.status],
              color: 'white'
            }}
          />
          <Chip
            label={`${lead.score} pts`}
            size="small"
            color={getScoreColor(lead.score)}
            variant="outlined"
          />
        </Box>
        
        <Typography variant="body2" color="textSecondary">
          Fuente: {lead.source}
        </Typography>
      </CardContent>
      
      <CardActions>
        <Button size="small" startIcon={<EmailIcon />}>
          Contactar
        </Button>
        {lead.status === 'qualified' && (
          <Button size="small" startIcon={<PersonAddIcon />} color="success">
            Convertir
          </Button>
        )}
      </CardActions>
    </Card>
  );

  const EmailTemplateCard = ({ template }) => (
    <Card sx={{ height: '100%' }}>
      <CardContent>
        <Box display="flex" justifyContent="space-between" alignItems="flex-start" mb={2}>
          <Avatar sx={{ bgcolor: 'secondary.main' }}>
            <EmailIcon />
          </Avatar>
          <IconButton size="small" onClick={(e) => handleMenuClick(e, template)}>
            <MoreVertIcon />
          </IconButton>
        </Box>
        
        <Typography variant="h6" gutterBottom noWrap>
          {template.name}
        </Typography>
        
        <Typography variant="body2" color="textSecondary" gutterBottom>
          {template.subject}
        </Typography>
        
        <Box display="flex" gap={1} mb={2}>
          <Chip
            label={template.is_active ? 'Activo' : 'Inactivo'}
            size="small"
            color={template.is_active ? 'success' : 'default'}
            variant="outlined"
          />
          <Chip
            label={`${template.times_used || 0} usos`}
            size="small"
            variant="outlined"
          />
        </Box>
        
        <Typography variant="body2" color="textSecondary">
          Creado: {new Date(template.created_at).toLocaleDateString('es-ES')}
        </Typography>
      </CardContent>
      
      <CardActions>
        <Button size="small" startIcon={<EditIcon />}>
          Editar
        </Button>
        <Button size="small" startIcon={<EmailIcon />}>
          Vista previa
        </Button>
      </CardActions>
    </Card>
  );

  const StatsCards = () => (
    <Grid container spacing={3} mb={3}>
      <Grid item xs={12} sm={6} md={3}>
        <Card>
          <CardContent>
            <Box display="flex" alignItems="center">
              <Avatar sx={{ bgcolor: 'primary.main', mr: 2 }}>
                <CampaignIcon />
              </Avatar>
              <Box>
                <Typography variant="h4" fontWeight="bold">
                  {marketingStats.totalCampaigns || 0}
                </Typography>
                <Typography variant="body2" color="textSecondary">
                  Campañas Totales
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
                <LaunchIcon />
              </Avatar>
              <Box>
                <Typography variant="h4" fontWeight="bold">
                  {marketingStats.activeCampaigns || 0}
                </Typography>
                <Typography variant="body2" color="textSecondary">
                  Campañas Activas
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
              <Avatar sx={{ bgcolor: 'info.main', mr: 2 }}>
                <PeopleIcon />
              </Avatar>
              <Box>
                <Typography variant="h4" fontWeight="bold">
                  {marketingStats.totalLeads || 0}
                </Typography>
                <Typography variant="body2" color="textSecondary">
                  Leads Totales
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
                <TrendingUpIcon />
              </Avatar>
              <Box>
                <Typography variant="h4" fontWeight="bold">
                  {marketingStats.conversionRate || 0}%
                </Typography>
                <Typography variant="body2" color="textSecondary">
                  Tasa de Conversión
                </Typography>
              </Box>
            </Box>
          </CardContent>
        </Card>
      </Grid>
    </Grid>
  );

  return (
    <Box>
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
        <Typography variant="h4" fontWeight="bold">
          Marketing
        </Typography>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={() => handleDialogOpen('create')}
        >
          {selectedTab === 0 ? 'Nueva Campaña' : selectedTab === 1 ? 'Nuevo Lead' : 'Nueva Plantilla'}
        </Button>
      </Box>

      <StatsCards />

      <Paper sx={{ mb: 3 }}>
        <Tabs
          value={selectedTab}
          onChange={(e, newValue) => setSelectedTab(newValue)}
          sx={{ borderBottom: 1, borderColor: 'divider' }}
        >
          <Tab label="Campañas" />
          <Tab label="Leads" />
          <Tab label="Plantillas Email" />
        </Tabs>
      </Paper>

      {loading ? (
        <Box display="flex" justifyContent="center" p={4}>
          <Typography>Cargando datos de marketing...</Typography>
        </Box>
      ) : (
        <Grid container spacing={3}>
          {selectedTab === 0 && campaigns.map((campaign) => (
            <Grid item xs={12} sm={6} md={4} key={campaign.id}>
              <CampaignCard campaign={campaign} />
            </Grid>
          ))}
          
          {selectedTab === 1 && leads.map((lead) => (
            <Grid item xs={12} sm={6} md={4} key={lead.id}>
              <LeadCard lead={lead} />
            </Grid>
          ))}
          
          {selectedTab === 2 && emailTemplates.map((template) => (
            <Grid item xs={12} sm={6} md={4} key={template.id}>
              <EmailTemplateCard template={template} />
            </Grid>
          ))}
        </Grid>
      )}

      {/* Menu contextual */}
      <Menu
        anchorEl={anchorEl}
        open={Boolean(anchorEl)}
        onClose={handleMenuClose}
      >
        <MenuItem onClick={() => handleDialogOpen('view')}>
          Ver detalles
        </MenuItem>
        <MenuItem onClick={() => handleDialogOpen('edit')}>
          <EditIcon sx={{ mr: 1 }} />
          Editar
        </MenuItem>
        {selectedTab === 0 && selectedItem?.status === 'planning' && (
          <MenuItem onClick={() => handleDialogOpen('activate')}>
            <LaunchIcon sx={{ mr: 1 }} />
            Activar campaña
          </MenuItem>
        )}
        {selectedTab === 1 && selectedItem?.status === 'qualified' && (
          <MenuItem onClick={() => handleDialogOpen('convert')}>
            <PersonAddIcon sx={{ mr: 1 }} />
            Convertir lead
          </MenuItem>
        )}
        <MenuItem onClick={() => handleDialogOpen('delete')}>
          <DeleteIcon sx={{ mr: 1 }} />
          Eliminar
        </MenuItem>
      </Menu>

      {/* Campaign Form Dialog */}
      <CampaignForm
        open={openDialog && selectedTab === 0 && (dialogType === 'create' || dialogType === 'edit')}
        onClose={handleDialogClose}
        campaign={dialogType === 'edit' ? selectedItem : null}
        onSave={loadData}
      />

      {/* Diálogo de confirmación de eliminación */}
      <Dialog open={openDialog && dialogType === 'delete'} onClose={handleDialogClose}>
        <DialogTitle>Confirmar eliminación</DialogTitle>
        <DialogContent>
          <Typography>
            ¿Estás seguro de que deseas eliminar este elemento?
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
    </Box>
  );
}

export default MarketingDashboard;