import React, { useEffect } from 'react';
import {
  Box,
  Grid,
  Card,
  CardContent,
  Typography,
  Avatar,
  IconButton,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  Chip,
  LinearProgress,
  List,
  ListItem,
  ListItemText,
  ListItemAvatar,
  Divider,
} from '@mui/material';
import {
  TrendingUp as TrendingUpIcon,
  People as PeopleIcon,
  Business as BusinessIcon,
  Assignment as AssignmentIcon,
  AttachMoney as AttachMoneyIcon,
  MoreVert as MoreVertIcon,
  Phone as PhoneIcon,
  Email as EmailIcon,
  VideoCall as MeetingIcon,
} from '@mui/icons-material';
import { PieChart, Pie, Cell, ResponsiveContainer, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, LineChart, Line } from 'recharts';

// Sample data - in a real app, this would come from API
const dashboardData = {
  kpis: [
    { title: 'Oportunidades Activas', value: '127', change: '+12%', icon: <TrendingUpIcon />, color: '#1976d2' },
    { title: 'Contactos Totales', value: '1,842', change: '+8%', icon: <PeopleIcon />, color: '#2e7d32' },
    { title: 'Cuentas Activas', value: '324', change: '+5%', icon: <BusinessIcon />, color: '#ed6c02' },
    { title: 'Tickets Abiertos', value: '23', change: '-15%', icon: <AssignmentIcon />, color: '#d32f2f' },
  ],
  salesPipeline: [
    { stage: 'Calificación', count: 45, value: 125000 },
    { stage: 'Análisis', count: 32, value: 280000 },
    { stage: 'Propuesta', count: 28, value: 420000 },
    { stage: 'Negociación', count: 15, value: 380000 },
    { stage: 'Cerrado', count: 7, value: 150000 },
  ],
  recentActivities: [
    { id: 1, type: 'call', contact: 'Carlos Mendoza', company: 'Tech Solutions SAC', time: '10:30 AM', status: 'completed' },
    { id: 2, type: 'email', contact: 'María García', company: 'Innovate Corp', time: '09:15 AM', status: 'sent' },
    { id: 3, type: 'meeting', contact: 'Juan Pérez', company: 'Digital Plus', time: '08:45 AM', status: 'scheduled' },
    { id: 4, type: 'call', contact: 'Ana Torres', company: 'Global Systems', time: 'Ayer 4:30 PM', status: 'missed' },
  ],
  topOpportunities: [
    { id: 1, name: 'Proyecto ERP - Tech Solutions', value: 85000, probability: 80, stage: 'Negociación' },
    { id: 2, name: 'Software CRM - Innovate Corp', value: 65000, probability: 60, stage: 'Propuesta' },
    { id: 3, name: 'Consultoría IT - Digital Plus', value: 45000, probability: 90, stage: 'Negociación' },
    { id: 4, name: 'Licencias Office - Global Systems', value: 32000, probability: 50, stage: 'Análisis' },
  ],
  monthlySales: [
    { month: 'Ene', value: 180000 },
    { month: 'Feb', value: 220000 },
    { month: 'Mar', value: 190000 },
    { month: 'Abr', value: 280000 },
    { month: 'May', value: 320000 },
    { month: 'Jun', value: 290000 },
  ],
};

const COLORS = ['#1976d2', '#2e7d32', '#ed6c02', '#d32f2f', '#7b1fa2'];

function Dashboard() {

  const getActivityIcon = (type) => {
    switch (type) {
      case 'call': return <PhoneIcon />;
      case 'email': return <EmailIcon />;
      case 'meeting': return <MeetingIcon />;
      default: return <AssignmentIcon />;
    }
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'completed': return 'success';
      case 'sent': return 'info';
      case 'scheduled': return 'warning';
      case 'missed': return 'error';
      default: return 'default';
    }
  };

  const getStageColor = (stage) => {
    switch (stage) {
      case 'Calificación': return 'info';
      case 'Análisis': return 'warning';
      case 'Propuesta': return 'primary';
      case 'Negociación': return 'secondary';
      default: return 'default';
    }
  };

  return (
    <Box>
      <Typography variant="h4" gutterBottom sx={{ fontWeight: 'bold', color: 'text.primary' }}>
        Dashboard
      </Typography>
      <Typography variant="subtitle1" gutterBottom sx={{ color: 'text.secondary', mb: 3 }}>
        Resumen de actividades y métricas clave del CRM
      </Typography>

      {/* KPI Cards */}
      <Grid container spacing={3} sx={{ mb: 3 }}>
        {dashboardData.kpis.map((kpi, index) => (
          <Grid item xs={12} sm={6} md={3} key={index}>
            <Card>
              <CardContent>
                <Box display="flex" alignItems="center" justifyContent="space-between">
                  <Box>
                    <Typography color="textSecondary" gutterBottom variant="body2">
                      {kpi.title}
                    </Typography>
                    <Typography variant="h4" component="div" sx={{ fontWeight: 'bold' }}>
                      {kpi.value}
                    </Typography>
                    <Typography 
                      variant="body2" 
                      sx={{ 
                        color: kpi.change.startsWith('+') ? 'success.main' : 'error.main',
                        fontWeight: 'medium'
                      }}
                    >
                      {kpi.change} vs mes anterior
                    </Typography>
                  </Box>
                  <Avatar sx={{ bgcolor: kpi.color, width: 56, height: 56 }}>
                    {kpi.icon}
                  </Avatar>
                </Box>
              </CardContent>
            </Card>
          </Grid>
        ))}
      </Grid>

      <Grid container spacing={3}>
        {/* Sales Pipeline Chart */}
        <Grid item xs={12} lg={8}>
          <Card>
            <CardContent>
              <Box display="flex" alignItems="center" justifyContent="space-between" mb={2}>
                <Typography variant="h6" fontWeight="bold">
                  Pipeline de Ventas
                </Typography>
                <IconButton size="small">
                  <MoreVertIcon />
                </IconButton>
              </Box>
              <ResponsiveContainer width="100%" height={300}>
                <BarChart data={dashboardData.salesPipeline}>
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis dataKey="stage" />
                  <YAxis />
                  <Tooltip formatter={(value) => [`$${value.toLocaleString()}`, 'Valor']} />
                  <Bar dataKey="value" fill="#1976d2" />
                </BarChart>
              </ResponsiveContainer>
            </CardContent>
          </Card>
        </Grid>

        {/* Recent Activities */}
        <Grid item xs={12} lg={4}>
          <Card>
            <CardContent>
              <Typography variant="h6" fontWeight="bold" gutterBottom>
                Actividades Recientes
              </Typography>
              <List disablePadding>
                {dashboardData.recentActivities.map((activity, index) => (
                  <React.Fragment key={activity.id}>
                    <ListItem disablePadding>
                      <ListItemAvatar>
                        <Avatar sx={{ bgcolor: 'primary.main', width: 40, height: 40 }}>
                          {getActivityIcon(activity.type)}
                        </Avatar>
                      </ListItemAvatar>
                      <ListItemText
                        primary={
                          <Box display="flex" alignItems="center" gap={1}>
                            <Typography variant="body2" fontWeight="medium">
                              {activity.contact}
                            </Typography>
                            <Chip 
                              label={activity.status} 
                              size="small" 
                              color={getStatusColor(activity.status)}
                              variant="outlined"
                            />
                          </Box>
                        }
                        secondary={
                          <Box>
                            <Typography variant="caption" color="textSecondary">
                              {activity.company}
                            </Typography>
                            <br />
                            <Typography variant="caption" color="textSecondary">
                              {activity.time}
                            </Typography>
                          </Box>
                        }
                      />
                    </ListItem>
                    {index < dashboardData.recentActivities.length - 1 && <Divider />}
                  </React.Fragment>
                ))}
              </List>
            </CardContent>
          </Card>
        </Grid>

        {/* Top Opportunities Table */}
        <Grid item xs={12} lg={8}>
          <Card>
            <CardContent>
              <Typography variant="h6" fontWeight="bold" gutterBottom>
                Principales Oportunidades
              </Typography>
              <TableContainer>
                <Table>
                  <TableHead>
                    <TableRow>
                      <TableCell>Oportunidad</TableCell>
                      <TableCell align="right">Valor</TableCell>
                      <TableCell align="center">Probabilidad</TableCell>
                      <TableCell align="center">Etapa</TableCell>
                    </TableRow>
                  </TableHead>
                  <TableBody>
                    {dashboardData.topOpportunities.map((opportunity) => (
                      <TableRow key={opportunity.id} hover>
                        <TableCell component="th" scope="row">
                          <Typography variant="body2" fontWeight="medium">
                            {opportunity.name}
                          </Typography>
                        </TableCell>
                        <TableCell align="right">
                          <Typography variant="body2" fontWeight="bold" color="success.main">
                            ${opportunity.value.toLocaleString()}
                          </Typography>
                        </TableCell>
                        <TableCell align="center">
                          <Box display="flex" alignItems="center" gap={1}>
                            <LinearProgress 
                              variant="determinate" 
                              value={opportunity.probability} 
                              sx={{ flex: 1, height: 6, borderRadius: 3 }}
                            />
                            <Typography variant="caption">
                              {opportunity.probability}%
                            </Typography>
                          </Box>
                        </TableCell>
                        <TableCell align="center">
                          <Chip 
                            label={opportunity.stage} 
                            size="small" 
                            color={getStageColor(opportunity.stage)}
                            variant="outlined"
                          />
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </TableContainer>
            </CardContent>
          </Card>
        </Grid>

        {/* Monthly Sales Trend */}
        <Grid item xs={12} lg={4}>
          <Card>
            <CardContent>
              <Typography variant="h6" fontWeight="bold" gutterBottom>
                Ventas Mensuales
              </Typography>
              <ResponsiveContainer width="100%" height={250}>
                <LineChart data={dashboardData.monthlySales}>
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis dataKey="month" />
                  <YAxis />
                  <Tooltip formatter={(value) => [`$${value.toLocaleString()}`, 'Ventas']} />
                  <Line 
                    type="monotone" 
                    dataKey="value" 
                    stroke="#1976d2" 
                    strokeWidth={3}
                    dot={{ fill: '#1976d2', strokeWidth: 2, r: 6 }}
                  />
                </LineChart>
              </ResponsiveContainer>
            </CardContent>
          </Card>
        </Grid>
      </Grid>
    </Box>
  );
}

export default Dashboard;