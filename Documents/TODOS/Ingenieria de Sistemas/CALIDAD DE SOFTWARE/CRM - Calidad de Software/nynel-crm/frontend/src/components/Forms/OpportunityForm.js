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
  Slider,
  InputAdornment
} from '@mui/material';
import { opportunitiesAPI, contactsAPI } from '../../services/api';

const stageOptions = [
  { value: 'qualification', label: 'Calificación' },
  { value: 'needs_analysis', label: 'Análisis de Necesidades' },
  { value: 'proposal', label: 'Propuesta' },
  { value: 'negotiation', label: 'Negociación' },
  { value: 'closed_won', label: 'Ganado' },
  { value: 'closed_lost', label: 'Perdido' }
];

function OpportunityForm({ open, onClose, opportunity = null, onSave }) {
  const [formData, setFormData] = useState({
    name: '',
    description: '',
    stage: 'qualification',
    amount: '',
    probability: 10,
    close_date: '',
    next_step: '',
    account: '',
    contact: '',
    campaign_source: '',
    type: 'new_business'
  });
  
  const [accounts, setAccounts] = useState([]);
  const [contacts, setContacts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});

  useEffect(() => {
    if (open) {
      loadAccounts();
      if (opportunity) {
        setFormData({
          name: opportunity.name || '',
          description: opportunity.description || '',
          stage: opportunity.stage || 'qualification',
          amount: opportunity.amount || '',
          probability: opportunity.probability || 10,
          close_date: opportunity.close_date || '',
          next_step: opportunity.next_step || '',
          account: opportunity.account || '',
          contact: opportunity.contact || '',
          campaign_source: opportunity.campaign_source || '',
          type: opportunity.type || 'new_business'
        });
        if (opportunity.account) {
          loadContacts(opportunity.account);
        }
      } else {
        setFormData({
          name: '',
          description: '',
          stage: 'qualification',
          amount: '',
          probability: 10,
          close_date: '',
          next_step: '',
          account: '',
          contact: '',
          campaign_source: '',
          type: 'new_business'
        });
        setContacts([]);
      }
      setErrors({});
    }
  }, [open, opportunity]);

  const loadAccounts = async () => {
    try {
      const response = await contactsAPI.getAccounts();
      setAccounts(response.data.results || response.data);
    } catch (error) {
      console.error('Error loading accounts:', error);
    }
  };

  const loadContacts = async (accountId) => {
    try {
      const response = await contactsAPI.getContacts({ account: accountId });
      setContacts(response.data.results || response.data);
    } catch (error) {
      console.error('Error loading contacts:', error);
    }
  };

  const handleChange = (field) => (event) => {
    const value = event.target.value;
    setFormData(prev => ({
      ...prev,
      [field]: value
    }));
    
    if (field === 'account') {
      setFormData(prev => ({ ...prev, contact: '' }));
      if (value) {
        loadContacts(value);
      } else {
        setContacts([]);
      }
    }
    
    if (errors[field]) {
      setErrors(prev => ({
        ...prev,
        [field]: ''
      }));
    }
  };

  const handleProbabilityChange = (event, newValue) => {
    setFormData(prev => ({
      ...prev,
      probability: newValue
    }));
  };

  const validateForm = () => {
    const newErrors = {};
    
    if (!formData.name.trim()) {
      newErrors.name = 'El nombre es requerido';
    }
    
    if (!formData.amount || formData.amount <= 0) {
      newErrors.amount = 'El monto debe ser mayor a 0';
    }
    
    if (!formData.close_date) {
      newErrors.close_date = 'La fecha de cierre es requerida';
    }
    
    if (!formData.account) {
      newErrors.account = 'La cuenta es requerida';
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
        amount: parseFloat(formData.amount)
      };
      
      if (opportunity) {
        await opportunitiesAPI.updateOpportunity(opportunity.id, data);
      } else {
        await opportunitiesAPI.createOpportunity(data);
      }
      onSave();
      onClose();
    } catch (error) {
      console.error('Error saving opportunity:', error);
      if (error.response?.data) {
        setErrors(error.response.data);
      }
    } finally {
      setLoading(false);
    }
  };

  const getProbabilityByStage = (stage) => {
    const probabilities = {
      'qualification': 10,
      'needs_analysis': 25,
      'proposal': 50,
      'negotiation': 75,
      'closed_won': 100,
      'closed_lost': 0
    };
    return probabilities[stage] || 10;
  };

  const handleStageChange = (event) => {
    const newStage = event.target.value;
    const suggestedProbability = getProbabilityByStage(newStage);
    
    setFormData(prev => ({
      ...prev,
      stage: newStage,
      probability: suggestedProbability
    }));
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
      <DialogTitle>
        {opportunity ? 'Editar Oportunidad' : 'Nueva Oportunidad'}
      </DialogTitle>
      
      <DialogContent>
        <Box sx={{ pt: 1 }}>
          <Grid container spacing={2}>
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Nombre de la Oportunidad *"
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
              <FormControl fullWidth error={!!errors.account}>
                <InputLabel>Cuenta *</InputLabel>
                <Select
                  value={formData.account}
                  onChange={handleChange('account')}
                  label="Cuenta *"
                >
                  {accounts.map((account) => (
                    <MenuItem key={account.id} value={account.id}>
                      {account.name}
                    </MenuItem>
                  ))}
                </Select>
                {errors.account && (
                  <Typography variant="caption" color="error" sx={{ ml: 2 }}>
                    {errors.account}
                  </Typography>
                )}
              </FormControl>
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <FormControl fullWidth>
                <InputLabel>Contacto</InputLabel>
                <Select
                  value={formData.contact}
                  onChange={handleChange('contact')}
                  label="Contacto"
                  disabled={!formData.account}
                >
                  {contacts.map((contact) => (
                    <MenuItem key={contact.id} value={contact.id}>
                      {contact.first_name} {contact.last_name}
                    </MenuItem>
                  ))}
                </Select>
              </FormControl>
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <FormControl fullWidth>
                <InputLabel>Etapa</InputLabel>
                <Select
                  value={formData.stage}
                  onChange={handleStageChange}
                  label="Etapa"
                >
                  {stageOptions.map((option) => (
                    <MenuItem key={option.value} value={option.value}>
                      {option.label}
                    </MenuItem>
                  ))}
                </Select>
              </FormControl>
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <FormControl fullWidth>
                <InputLabel>Tipo</InputLabel>
                <Select
                  value={formData.type}
                  onChange={handleChange('type')}
                  label="Tipo"
                >
                  <MenuItem value="new_business">Nuevo Negocio</MenuItem>
                  <MenuItem value="existing_business">Negocio Existente</MenuItem>
                  <MenuItem value="amendment">Enmienda</MenuItem>
                </Select>
              </FormControl>
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Monto *"
                type="number"
                value={formData.amount}
                onChange={handleChange('amount')}
                error={!!errors.amount}
                helperText={errors.amount}
                InputProps={{
                  startAdornment: <InputAdornment position="start">S/</InputAdornment>,
                }}
              />
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Fecha de Cierre *"
                type="date"
                value={formData.close_date}
                onChange={handleChange('close_date')}
                error={!!errors.close_date}
                helperText={errors.close_date}
                InputLabelProps={{ shrink: true }}
              />
            </Grid>
            
            <Grid item xs={12}>
              <Typography gutterBottom>
                Probabilidad: {formData.probability}%
              </Typography>
              <Slider
                value={formData.probability}
                onChange={handleProbabilityChange}
                aria-labelledby="probability-slider"
                valueLabelDisplay="auto"
                step={5}
                marks
                min={0}
                max={100}
                sx={{ mb: 2 }}
              />
            </Grid>
            
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Siguiente Paso"
                value={formData.next_step}
                onChange={handleChange('next_step')}
                multiline
                rows={2}
              />
            </Grid>
            
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Fuente de Campaña"
                value={formData.campaign_source}
                onChange={handleChange('campaign_source')}
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
          {loading ? 'Guardando...' : opportunity ? 'Actualizar' : 'Crear'}
        </Button>
      </DialogActions>
    </Dialog>
  );
}

export default OpportunityForm;