import React, { useState, useEffect } from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  TextField,
  Grid,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Box,
  Typography,
  InputAdornment,
  FormControlLabel,
  Switch
} from '@mui/material';
import { marketingAPI } from '../../services/api';

const campaignTypes = [
  { value: 'email', label: 'Email Marketing' },
  { value: 'social_media', label: 'Redes Sociales' },
  { value: 'paid_ads', label: 'Publicidad Pagada' },
  { value: 'webinar', label: 'Webinar' },
  { value: 'trade_show', label: 'Feria Comercial' },
  { value: 'content_marketing', label: 'Marketing de Contenido' },
  { value: 'referral', label: 'Referencias' },
  { value: 'direct_mail', label: 'Correo Directo' },
  { value: 'telemarketing', label: 'Telemarketing' },
  { value: 'other', label: 'Otro' }
];

const campaignStatuses = [
  { value: 'planning', label: 'Planificación' },
  { value: 'active', label: 'Activo' },
  { value: 'paused', label: 'Pausado' },
  { value: 'completed', label: 'Completado' },
  { value: 'cancelled', label: 'Cancelado' }
];

function CampaignForm({ open, onClose, campaign = null, onSave }) {
  const [formData, setFormData] = useState({
    name: '',
    description: '',
    campaign_type: 'email',
    status: 'planning',
    start_date: '',
    end_date: '',
    budget: '',
    expected_leads: '',
    expected_revenue: '',
    target_audience: '',
    message: '',
    is_active: true
  });
  
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});

  useEffect(() => {
    if (open) {
      if (campaign) {
        setFormData({
          name: campaign.name || '',
          description: campaign.description || '',
          campaign_type: campaign.campaign_type || 'email',
          status: campaign.status || 'planning',
          start_date: campaign.start_date || '',
          end_date: campaign.end_date || '',
          budget: campaign.budget || '',
          expected_leads: campaign.expected_leads || '',
          expected_revenue: campaign.expected_revenue || '',
          target_audience: campaign.target_audience || '',
          message: campaign.message || '',
          is_active: campaign.is_active !== undefined ? campaign.is_active : true
        });
      } else {
        const today = new Date().toISOString().split('T')[0];
        const nextMonth = new Date();
        nextMonth.setMonth(nextMonth.getMonth() + 1);
        
        setFormData({
          name: '',
          description: '',
          campaign_type: 'email',
          status: 'planning',
          start_date: today,
          end_date: nextMonth.toISOString().split('T')[0],
          budget: '',
          expected_leads: '',
          expected_revenue: '',
          target_audience: '',
          message: '',
          is_active: true
        });
      }
      setErrors({});
    }
  }, [open, campaign]);

  const handleChange = (field) => (event) => {
    const value = event.target.type === 'checkbox' ? event.target.checked : event.target.value;
    setFormData(prev => ({
      ...prev,
      [field]: value
    }));
    
    if (errors[field]) {
      setErrors(prev => ({
        ...prev,
        [field]: ''
      }));
    }
  };

  const validateForm = () => {
    const newErrors = {};
    
    if (!formData.name.trim()) {
      newErrors.name = 'El nombre es requerido';
    }
    
    if (!formData.start_date) {
      newErrors.start_date = 'La fecha de inicio es requerida';
    }
    
    if (!formData.end_date) {
      newErrors.end_date = 'La fecha de fin es requerida';
    }
    
    if (formData.start_date && formData.end_date && formData.start_date >= formData.end_date) {
      newErrors.end_date = 'La fecha de fin debe ser posterior a la fecha de inicio';
    }
    
    if (formData.budget && formData.budget <= 0) {
      newErrors.budget = 'El presupuesto debe ser mayor a 0';
    }
    
    if (formData.expected_leads && formData.expected_leads <= 0) {
      newErrors.expected_leads = 'Los leads esperados deben ser mayor a 0';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async () => {
    if (!validateForm()) return;
    
    try {
      setLoading(true);
      const data = {
        ...formData,
        budget: formData.budget ? parseFloat(formData.budget) : null,
        expected_leads: formData.expected_leads ? parseInt(formData.expected_leads) : null,
        expected_revenue: formData.expected_revenue ? parseFloat(formData.expected_revenue) : null
      };
      
      if (campaign) {
        await marketingAPI.updateCampaign(campaign.id, data);
      } else {
        await marketingAPI.createCampaign(data);
      }
      onSave();
      onClose();
    } catch (error) {
      console.error('Error saving campaign:', error);
      if (error.response?.data) {
        setErrors(error.response.data);
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
      <DialogTitle>
        {campaign ? 'Editar Campaña' : 'Nueva Campaña'}
      </DialogTitle>
      
      <DialogContent>
        <Box sx={{ pt: 1 }}>
          <Grid container spacing={2}>
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Nombre de la Campaña *"
                value={formData.name}
                onChange={handleChange('name')}
                error={!!errors.name}
                helperText={errors.name}
              />
            </Grid>
            
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Descripción"
                value={formData.description}
                onChange={handleChange('description')}
                multiline
                rows={3}
              />
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <FormControl fullWidth>
                <InputLabel>Tipo de Campaña</InputLabel>
                <Select
                  value={formData.campaign_type}
                  onChange={handleChange('campaign_type')}
                  label="Tipo de Campaña"
                >
                  {campaignTypes.map((type) => (
                    <MenuItem key={type.value} value={type.value}>
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
                  value={formData.status}
                  onChange={handleChange('status')}
                  label="Estado"
                >
                  {campaignStatuses.map((status) => (
                    <MenuItem key={status.value} value={status.value}>
                      {status.label}
                    </MenuItem>
                  ))}
                </Select>
              </FormControl>
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Fecha de Inicio *"
                type="date"
                value={formData.start_date}
                onChange={handleChange('start_date')}
                error={!!errors.start_date}
                helperText={errors.start_date}
                InputLabelProps={{ shrink: true }}
              />
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Fecha de Fin *"
                type="date"
                value={formData.end_date}
                onChange={handleChange('end_date')}
                error={!!errors.end_date}
                helperText={errors.end_date}
                InputLabelProps={{ shrink: true }}
              />
            </Grid>
            
            <Grid item xs={12} sm={4}>
              <TextField
                fullWidth
                label="Presupuesto"
                type="number"
                value={formData.budget}
                onChange={handleChange('budget')}
                error={!!errors.budget}
                helperText={errors.budget}
                InputProps={{
                  startAdornment: <InputAdornment position="start">S/</InputAdornment>,
                }}
              />
            </Grid>
            
            <Grid item xs={12} sm={4}>
              <TextField
                fullWidth
                label="Leads Esperados"
                type="number"
                value={formData.expected_leads}
                onChange={handleChange('expected_leads')}
                error={!!errors.expected_leads}
                helperText={errors.expected_leads}
              />
            </Grid>
            
            <Grid item xs={12} sm={4}>
              <TextField
                fullWidth
                label="Ingresos Esperados"
                type="number"
                value={formData.expected_revenue}
                onChange={handleChange('expected_revenue')}
                InputProps={{
                  startAdornment: <InputAdornment position="start">S/</InputAdornment>,
                }}
              />
            </Grid>
            
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Audiencia Objetivo"
                value={formData.target_audience}
                onChange={handleChange('target_audience')}
                multiline
                rows={2}
                placeholder="Describe el público objetivo de esta campaña..."
              />
            </Grid>
            
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Mensaje de la Campaña"
                value={formData.message}
                onChange={handleChange('message')}
                multiline
                rows={3}
                placeholder="Describe el mensaje principal de la campaña..."
              />
            </Grid>
            
            <Grid item xs={12}>
              <FormControlLabel
                control={
                  <Switch
                    checked={formData.is_active}
                    onChange={handleChange('is_active')}
                  />
                }
                label="Campaña activa"
              />
            </Grid>
          </Grid>
        </Box>
      </DialogContent>
      
      <DialogActions>
        <Button onClick={onClose}>
          Cancelar
        </Button>
        <Button 
          onClick={handleSubmit}
          variant="contained"
          disabled={loading}
        >
          {loading ? 'Guardando...' : campaign ? 'Actualizar' : 'Crear'}
        </Button>
      </DialogActions>
    </Dialog>
  );
}

export default CampaignForm;