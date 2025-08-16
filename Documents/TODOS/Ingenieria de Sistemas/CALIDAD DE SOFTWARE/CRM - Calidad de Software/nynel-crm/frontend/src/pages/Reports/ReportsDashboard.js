import React, { useState, useEffect } from 'react';
import {
  Box,
  Grid,
  Card,
  CardContent,
  Typography,
  Button,
  Paper,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Avatar,
  Chip,
  LinearProgress,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  TextField
} from '@mui/material';
import {
  Assessment as AssessmentIcon,
  TrendingUp as TrendingUpIcon,
  People as PeopleIcon,
  AttachMoney as MoneyIcon,
  Assignment as AssignmentIcon,
  Campaign as CampaignIcon,
  FileDownload as DownloadIcon,
  DateRange as DateRangeIcon
} from '@mui/icons-material';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, PieChart, Pie, Cell, LineChart, Line, ResponsiveContainer } from 'recharts';

const COLORS = ['#1976d2', '#2e7d32', '#ed6c02', '#d32f2f', '#7b1fa2'];

// Datos demo para reportes
const salesData = [
  { month: 'Ene', ventas: 180000, oportunidades: 15, contactos: 45 },
  { month: 'Feb', ventas: 220000, oportunidades: 18, contactos: 52 },
  { month: 'Mar', ventas: 190000, oportunidades: 12, contactos: 38 },
  { month: 'Abr', ventas: 280000, oportunidades: 22, contactos: 65 },
  { month: 'May', ventas: 320000, oportunidades: 25, contactos: 73 },
  { month: 'Jun', ventas: 290000, oportunidades: 20, contactos: 58 }
];

const pipelineData = [
  { name: 'Calificación', value: 35, amount: 175000 },
  { name: 'Análisis', value: 25, amount: 125000 },
  { name: 'Propuesta', value: 20, amount: 100000 },
  { name: 'Negociación', value: 15, amount: 75000 },
  { name: 'Cerrado', value: 5, amount: 25000 }
];

const supportData = [
  { month: 'Ene', tickets: 45, resueltos: 42, satisfaccion: 4.2 },
  { month: 'Feb', tickets: 52, resueltos: 48, satisfaccion: 4.1 },
  { month: 'Mar', tickets: 38, resueltos: 36, satisfaccion: 4.3 },
  { month: 'Abr', tickets: 65, resueltos: 60, satisfaccion: 4.0 },
  { month: 'May', tickets: 73, resueltos: 70, satisfaccion: 4.4 },
  { month: 'Jun', tickets: 58, resueltos: 55, satisfaccion: 4.2 }
];

const topPerformers = [
  { name: 'Carlos Mendoza', ventas: 85000, oportunidades: 8, conversion: 75 },
  { name: 'María García', ventas: 72000, oportunidades: 12, conversion: 60 },
  { name: 'Juan Pérez', ventas: 68000, oportunidades: 10, conversion: 68 },
  { name: 'Ana Torres', ventas: 55000, oportunidades: 9, conversion: 56 }
];

function ReportsDashboard() {
  const [selectedReport, setSelectedReport] = useState('sales');
  const [dateRange, setDateRange] = useState('6months');
  const [loading, setLoading] = useState(false);


  const handleExportReport = (format) => {
    console.log(`Exporting ${selectedReport} report as ${format}`);
    // Simular descarga
    alert(`Exportando reporte de ${selectedReport} en formato ${format.toUpperCase()}`);
  };

  const StatsCard = ({ title, value, icon, color, subtitle }) => (
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
            {subtitle && (
              <Typography variant="caption" color="success.main">
                {subtitle}
              </Typography>
            )}
          </Box>
        </Box>
      </CardContent>
    </Card>
  );

  const renderSalesReport = () => (
    <Grid container spacing={3}>
      <Grid item xs={12} sm={6} md={3}>
        <StatsCard
          title="Ventas Totales"
          value="S/ 1,480,000"
          subtitle="+15% vs mes anterior"
          icon={<MoneyIcon />}
          color="success.main"
        />
      </Grid>
      <Grid item xs={12} sm={6} md={3}>
        <StatsCard
          title="Oportunidades"
          value="112"
          subtitle="+8% vs mes anterior"
          icon={<TrendingUpIcon />}
          color="primary.main"
        />
      </Grid>
      <Grid item xs={12} sm={6} md={3}>
        <StatsCard
          title="Tasa Conversión"
          value="65%"
          subtitle="+3% vs mes anterior"
          icon={<AssessmentIcon />}
          color="warning.main"
        />
      </Grid>
      <Grid item xs={12} sm={6} md={3}>
        <StatsCard
          title="Valor Promedio"
          value="S/ 13,214"
          subtitle="+5% vs mes anterior"
          icon={<MoneyIcon />}
          color="info.main"
        />
      </Grid>

      <Grid item xs={12} md={8}>
        <Paper sx={{ p: 3 }}>
          <Typography variant="h6" gutterBottom>
            Tendencia de Ventas
          </Typography>
          <ResponsiveContainer width="100%" height={300}>
            <BarChart data={salesData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="month" />
              <YAxis />
              <Tooltip formatter={(value) => [`S/ ${value.toLocaleString()}`, 'Ventas']} />
              <Bar dataKey="ventas" fill="#1976d2" />
            </BarChart>
          </ResponsiveContainer>
        </Paper>
      </Grid>

      <Grid item xs={12} md={4}>
        <Paper sx={{ p: 3 }}>
          <Typography variant="h6" gutterBottom>
            Pipeline de Ventas
          </Typography>
          <ResponsiveContainer width="100%" height={300}>
            <PieChart>
              <Pie
                data={pipelineData}
                cx="50%"
                cy="50%"
                outerRadius={80}
                fill="#8884d8"
                dataKey="value"
                label={({ name, value }) => `${name}: ${value}%`}
              >
                {pipelineData.map((entry, index) => (
                  <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                ))}
              </Pie>
              <Tooltip />
            </PieChart>
          </ResponsiveContainer>
        </Paper>
      </Grid>

      <Grid item xs={12}>
        <Paper sx={{ p: 3 }}>
          <Typography variant="h6" gutterBottom>
            Top Performers
          </Typography>
          <TableContainer>
            <Table>
              <TableHead>
                <TableRow>
                  <TableCell>Vendedor</TableCell>
                  <TableCell>Ventas</TableCell>
                  <TableCell>Oportunidades</TableCell>
                  <TableCell>Conversión</TableCell>
                  <TableCell>Rendimiento</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {topPerformers.map((performer, index) => (
                  <TableRow key={index}>
                    <TableCell>
                      <Box display="flex" alignItems="center">
                        <Avatar sx={{ mr: 2, bgcolor: 'primary.main' }}>
                          {performer.name.charAt(0)}
                        </Avatar>
                        {performer.name}
                      </Box>
                    </TableCell>
                    <TableCell>S/ {performer.ventas.toLocaleString()}</TableCell>
                    <TableCell>{performer.oportunidades}</TableCell>
                    <TableCell>{performer.conversion}%</TableCell>
                    <TableCell>
                      <LinearProgress
                        variant="determinate"
                        value={performer.conversion}
                        sx={{ width: 100, height: 8, borderRadius: 4 }}
                      />
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </TableContainer>
        </Paper>
      </Grid>
    </Grid>
  );

  const renderSupportReport = () => (
    <Grid container spacing={3}>
      <Grid item xs={12} sm={6} md={3}>
        <StatsCard
          title="Tickets Totales"
          value="331"
          subtitle="+12% vs mes anterior"
          icon={<AssignmentIcon />}
          color="primary.main"
        />
      </Grid>
      <Grid item xs={12} sm={6} md={3}>
        <StatsCard
          title="Tasa Resolución"
          value="94%"
          subtitle="+2% vs mes anterior"
          icon={<AssessmentIcon />}
          color="success.main"
        />
      </Grid>
      <Grid item xs={12} sm={6} md={3}>
        <StatsCard
          title="Satisfacción"
          value="4.2/5"
          subtitle="+0.1 vs mes anterior"
          icon={<AssignmentIcon />}
          color="warning.main"
        />
      </Grid>
      <Grid item xs={12} sm={6} md={3}>
        <StatsCard
          title="Tiempo Promedio"
          value="2.3h"
          subtitle="-0.2h vs mes anterior"
          icon={<AssignmentIcon />}
          color="info.main"
        />
      </Grid>

      <Grid item xs={12}>
        <Paper sx={{ p: 3 }}>
          <Typography variant="h6" gutterBottom>
            Tendencia de Tickets de Soporte
          </Typography>
          <ResponsiveContainer width="100%" height={300}>
            <LineChart data={supportData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="month" />
              <YAxis />
              <Tooltip />
              <Line type="monotone" dataKey="tickets" stroke="#1976d2" name="Tickets" />
              <Line type="monotone" dataKey="resueltos" stroke="#2e7d32" name="Resueltos" />
            </LineChart>
          </ResponsiveContainer>
        </Paper>
      </Grid>
    </Grid>
  );

  return (
    <Box>
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
        <Typography variant="h4" fontWeight="bold">
          Reportes y Análisis
        </Typography>
        <Box display="flex" gap={1}>
          <Button
            variant="outlined"
            startIcon={<DownloadIcon />}
            onClick={() => handleExportReport('pdf')}
          >
            Exportar PDF
          </Button>
          <Button
            variant="outlined"
            startIcon={<DownloadIcon />}
            onClick={() => handleExportReport('excel')}
          >
            Exportar Excel
          </Button>
        </Box>
      </Box>

      <Paper sx={{ p: 2, mb: 3 }}>
        <Grid container spacing={2} alignItems="center">
          <Grid item xs={12} sm={4}>
            <FormControl fullWidth>
              <InputLabel>Tipo de Reporte</InputLabel>
              <Select
                value={selectedReport}
                onChange={(e) => setSelectedReport(e.target.value)}
                label="Tipo de Reporte"
              >
                <MenuItem value="sales">Reporte de Ventas</MenuItem>
                <MenuItem value="support">Reporte de Soporte</MenuItem>
                <MenuItem value="marketing">Reporte de Marketing</MenuItem>
                <MenuItem value="general">Reporte General</MenuItem>
              </Select>
            </FormControl>
          </Grid>
          <Grid item xs={12} sm={4}>
            <FormControl fullWidth>
              <InputLabel>Período</InputLabel>
              <Select
                value={dateRange}
                onChange={(e) => setDateRange(e.target.value)}
                label="Período"
              >
                <MenuItem value="1month">Último mes</MenuItem>
                <MenuItem value="3months">Últimos 3 meses</MenuItem>
                <MenuItem value="6months">Últimos 6 meses</MenuItem>
                <MenuItem value="1year">Último año</MenuItem>
              </Select>
            </FormControl>
          </Grid>
          <Grid item xs={12} sm={4}>
            <Button
              variant="contained"
              fullWidth
              startIcon={<AssessmentIcon />}
              onClick={() => setLoading(!loading)}
            >
              Generar Reporte
            </Button>
          </Grid>
        </Grid>
      </Paper>

      {loading && <LinearProgress sx={{ mb: 3 }} />}

      {selectedReport === 'sales' && renderSalesReport()}
      {selectedReport === 'support' && renderSupportReport()}
      
      {(selectedReport === 'marketing' || selectedReport === 'general') && (
        <Paper sx={{ p: 4, textAlign: 'center' }}>
          <AssessmentIcon sx={{ fontSize: 64, color: 'text.secondary', mb: 2 }} />
          <Typography variant="h6" color="textSecondary">
            Reporte de {selectedReport === 'marketing' ? 'Marketing' : 'General'} 
          </Typography>
          <Typography variant="body2" color="textSecondary">
            Este reporte estará disponible próximamente
          </Typography>
        </Paper>
      )}
    </Box>
  );
}

export default ReportsDashboard;