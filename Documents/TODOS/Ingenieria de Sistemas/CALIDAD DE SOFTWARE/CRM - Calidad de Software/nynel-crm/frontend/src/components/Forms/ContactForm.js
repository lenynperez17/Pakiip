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
  Divider
} from '@mui/material';
import { contactsAPI } from '../../services/api';

function ContactForm({ open, onClose, contact = null, onSave }) {
  const [formData, setFormData] = useState({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    mobile: '',
    job_title: '',
    department: '',
    dni: '',
    birth_date: '',
    lead_source: 'website',
    mailing_street: '',
    mailing_city: 'Lima',
    mailing_state: 'Lima',
    mailing_country: 'Perú',
    description: '',
    account: ''
  });
  
  const [accounts, setAccounts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});

  useEffect(() => {
    if (open) {
      loadAccounts();
      if (contact) {
        setFormData({
          first_name: contact.first_name || '',
          last_name: contact.last_name || '',
          email: contact.email || '',
          phone: contact.phone || '',
          mobile: contact.mobile || '',
          job_title: contact.job_title || '',
          department: contact.department || '',
          dni: contact.dni || '',
          birth_date: contact.birth_date || '',
          lead_source: contact.lead_source || 'website',
          mailing_street: contact.mailing_street || '',
          mailing_city: contact.mailing_city || 'Lima',
          mailing_state: contact.mailing_state || 'Lima',
          mailing_country: contact.mailing_country || 'Perú',
          description: contact.description || '',
          account: contact.account || ''
        });
      } else {
        setFormData({
          first_name: '',
          last_name: '',
          email: '',
          phone: '',
          mobile: '',
          job_title: '',
          department: '',
          dni: '',
          birth_date: '',
          lead_source: 'website',
          mailing_street: '',
          mailing_city: 'Lima',
          mailing_state: 'Lima',
          mailing_country: 'Perú',
          description: '',
          account: ''
        });
      }
      setErrors({});
    }
  }, [open, contact]);

  const loadAccounts = async () => {
    try {
      const response = await contactsAPI.getAccounts();
      setAccounts(response.data.results || response.data);
    } catch (error) {
      console.error('Error loading accounts:', error);
    }
  };

  const handleChange = (field) => (event) => {
    setFormData(prev => ({
      ...prev,
      [field]: event.target.value
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
    
    if (!formData.first_name.trim()) {
      newErrors.first_name = 'El nombre es requerido';
    }
    
    if (!formData.last_name.trim()) {
      newErrors.last_name = 'El apellido es requerido';
    }
    
    if (!formData.email.trim()) {
      newErrors.email = 'El email es requerido';
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'El email no es válido';
    }
    
    if (formData.dni && !/^\d{8}$/.test(formData.dni)) {
      newErrors.dni = 'El DNI debe tener 8 dígitos';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async () => {
    if (!validateForm()) return;
    
    try {
      setLoading(true);
      if (contact) {
        await contactsAPI.updateContact(contact.id, formData);
      } else {
        await contactsAPI.createContact(formData);
      }
      onSave();
      onClose();
    } catch (error) {
      console.error('Error saving contact:', error);
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
        {contact ? 'Editar Contacto' : 'Nuevo Contacto'}
      </DialogTitle>
      
      <DialogContent>
        <Box sx={{ pt: 1 }}>
          <Typography variant="h6" gutterBottom>
            Información Personal
          </Typography>
          
          <Grid container spacing={2} sx={{ mb: 3 }}>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Nombre *"
                value={formData.first_name}
                onChange={handleChange('first_name')}
                error={!!errors.first_name}
                helperText={errors.first_name}
              />
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Apellido *"
                value={formData.last_name}
                onChange={handleChange('last_name')}
                error={!!errors.last_name}
                helperText={errors.last_name}
              />
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Email *"
                type="email"
                value={formData.email}
                onChange={handleChange('email')}
                error={!!errors.email}
                helperText={errors.email}
              />
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="DNI"
                value={formData.dni}
                onChange={handleChange('dni')}
                error={!!errors.dni}
                helperText={errors.dni}
                inputProps={{ maxLength: 8 }}
              />
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Teléfono"
                value={formData.phone}
                onChange={handleChange('phone')}
              />
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Móvil"
                value={formData.mobile}
                onChange={handleChange('mobile')}
              />
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Fecha de Nacimiento"
                type="date"
                value={formData.birth_date}
                onChange={handleChange('birth_date')}
                InputLabelProps={{ shrink: true }}
              />
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <FormControl fullWidth>
                <InputLabel>Fuente de Lead</InputLabel>
                <Select
                  value={formData.lead_source}
                  onChange={handleChange('lead_source')}
                  label="Fuente de Lead"
                >
                  <MenuItem value="website">Sitio Web</MenuItem>
                  <MenuItem value="referral">Referencia</MenuItem>
                  <MenuItem value="social_media">Redes Sociales</MenuItem>
                  <MenuItem value="email_campaign">Campaña Email</MenuItem>
                  <MenuItem value="trade_show">Feria</MenuItem>
                  <MenuItem value="cold_call">Llamada Fría</MenuItem>
                  <MenuItem value="other">Otro</MenuItem>
                </Select>
              </FormControl>
            </Grid>
          </Grid>

          <Divider sx={{ my: 2 }} />
          
          <Typography variant="h6" gutterBottom>
            Información Profesional
          </Typography>
          
          <Grid container spacing={2} sx={{ mb: 3 }}>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Cargo"
                value={formData.job_title}
                onChange={handleChange('job_title')}
              />
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Departamento"
                value={formData.department}
                onChange={handleChange('department')}
              />
            </Grid>
            
            <Grid item xs={12}>
              <FormControl fullWidth>
                <InputLabel>Cuenta</InputLabel>
                <Select
                  value={formData.account}
                  onChange={handleChange('account')}
                  label="Cuenta"
                >
                  <MenuItem value="">Sin cuenta</MenuItem>
                  {accounts.map((account) => (
                    <MenuItem key={account.id} value={account.id}>
                      {account.name}
                    </MenuItem>
                  ))}
                </Select>
              </FormControl>
            </Grid>
          </Grid>

          <Divider sx={{ my: 2 }} />
          
          <Typography variant="h6" gutterBottom>
            Dirección
          </Typography>
          
          <Grid container spacing={2} sx={{ mb: 3 }}>
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Dirección"
                value={formData.mailing_street}
                onChange={handleChange('mailing_street')}
                multiline
                rows={2}
              />
            </Grid>
            
            <Grid item xs={12} sm={4}>
              <TextField
                fullWidth
                label="Ciudad"
                value={formData.mailing_city}
                onChange={handleChange('mailing_city')}
              />
            </Grid>
            
            <Grid item xs={12} sm={4}>
              <TextField
                fullWidth
                label="Estado/Región"
                value={formData.mailing_state}
                onChange={handleChange('mailing_state')}
              />
            </Grid>
            
            <Grid item xs={12} sm={4}>
              <TextField
                fullWidth
                label="País"
                value={formData.mailing_country}
                onChange={handleChange('mailing_country')}
              />
            </Grid>
          </Grid>

          <TextField
            fullWidth
            label="Descripción"
            value={formData.description}
            onChange={handleChange('description')}
            multiline
            rows={3}
            sx={{ mb: 2 }}
          />
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
          {loading ? 'Guardando...' : contact ? 'Actualizar' : 'Crear'}
        </Button>
      </DialogActions>
    </Dialog>
  );
}

export default ContactForm;