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
  Divider,
  InputAdornment
} from '@mui/material';
import { contactsAPI } from '../../services/api';

function AccountForm({ open, onClose, account = null, onSave }) {
  const [formData, setFormData] = useState({
    name: '',
    ruc: '',
    account_type: 'prospect',
    industry: '',
    annual_revenue: '',
    employees: '',
    website: '',
    phone: '',
    email: '',
    billing_street: '',
    billing_city: 'Lima',
    billing_state: 'Lima',
    billing_country: 'Perú',
    billing_postal_code: '',
    shipping_street: '',
    shipping_city: 'Lima',
    shipping_state: 'Lima',
    shipping_country: 'Perú',
    shipping_postal_code: '',
    description: '',
    parent_account: ''
  });
  
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});

  useEffect(() => {
    if (open) {
      if (account) {
        setFormData({
          name: account.name || '',
          ruc: account.ruc || '',
          account_type: account.account_type || 'prospect',
          industry: account.industry || '',
          annual_revenue: account.annual_revenue || '',
          employees: account.employees || '',
          website: account.website || '',
          phone: account.phone || '',
          email: account.email || '',
          billing_street: account.billing_street || '',
          billing_city: account.billing_city || 'Lima',
          billing_state: account.billing_state || 'Lima',
          billing_country: account.billing_country || 'Perú',
          billing_postal_code: account.billing_postal_code || '',
          shipping_street: account.shipping_street || '',
          shipping_city: account.shipping_city || 'Lima',
          shipping_state: account.shipping_state || 'Lima',
          shipping_country: account.shipping_country || 'Perú',
          shipping_postal_code: account.shipping_postal_code || '',
          description: account.description || '',
          parent_account: account.parent_account || ''
        });
      } else {
        setFormData({
          name: '',
          ruc: '',
          account_type: 'prospect',
          industry: '',
          annual_revenue: '',
          employees: '',
          website: '',
          phone: '',
          email: '',
          billing_street: '',
          billing_city: 'Lima',
          billing_state: 'Lima',
          billing_country: 'Perú',
          billing_postal_code: '',
          shipping_street: '',
          shipping_city: 'Lima',
          shipping_state: 'Lima',
          shipping_country: 'Perú',
          shipping_postal_code: '',
          description: '',
          parent_account: ''
        });
      }
      setErrors({});
    }
  }, [open, account]);

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
    
    if (!formData.name.trim()) {
      newErrors.name = 'El nombre de la empresa es requerido';
    }
    
    if (!formData.ruc.trim()) {
      newErrors.ruc = 'El RUC es requerido';
    } else if (!/^\d{11}$/.test(formData.ruc)) {
      newErrors.ruc = 'El RUC debe tener 11 dígitos';
    }
    
    if (formData.email && !/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'El email no es válido';
    }
    
    if (formData.website && !formData.website.startsWith('http')) {
      setFormData(prev => ({
        ...prev,
        website: 'https://' + formData.website
      }));
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
        annual_revenue: formData.annual_revenue ? parseFloat(formData.annual_revenue) : null,
        employees: formData.employees ? parseInt(formData.employees) : null
      };
      
      if (account) {
        await contactsAPI.updateAccount(account.id, data);
      } else {
        await contactsAPI.createAccount(data);
      }
      onSave();
      onClose();
    } catch (error) {
      console.error('Error saving account:', error);
      if (error.response?.data) {
        setErrors(error.response.data);
      }
    } finally {
      setLoading(false);
    }
  };

  const copyBillingToShipping = () => {
    setFormData(prev => ({
      ...prev,
      shipping_street: prev.billing_street,
      shipping_city: prev.billing_city,
      shipping_state: prev.billing_state,
      shipping_country: prev.billing_country,
      shipping_postal_code: prev.billing_postal_code
    }));
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="lg" fullWidth>
      <DialogTitle>
        {account ? 'Editar Cuenta' : 'Nueva Cuenta'}
      </DialogTitle>
      
      <DialogContent>
        <Box sx={{ pt: 1 }}>
          <Typography variant="h6" gutterBottom>
            Información Básica
          </Typography>
          
          <Grid container spacing={2} sx={{ mb: 3 }}>
            <Grid item xs={12} sm={8}>
              <TextField
                fullWidth
                label="Nombre de la Empresa *"
                value={formData.name}
                onChange={handleChange('name')}
                error={!!errors.name}
                helperText={errors.name}
              />
            </Grid>
            
            <Grid item xs={12} sm={4}>
              <TextField
                fullWidth
                label="RUC *"
                value={formData.ruc}
                onChange={handleChange('ruc')}
                error={!!errors.ruc}
                helperText={errors.ruc}
                inputProps={{ maxLength: 11 }}
              />
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <FormControl fullWidth>
                <InputLabel>Tipo de Cuenta</InputLabel>
                <Select
                  value={formData.account_type}
                  onChange={handleChange('account_type')}
                  label="Tipo de Cuenta"
                >
                  <MenuItem value="prospect">Prospecto</MenuItem>
                  <MenuItem value="customer">Cliente</MenuItem>
                  <MenuItem value="partner">Socio</MenuItem>
                  <MenuItem value="competitor">Competidor</MenuItem>
                  <MenuItem value="vendor">Proveedor</MenuItem>
                </Select>
              </FormControl>
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Industria"
                value={formData.industry}
                onChange={handleChange('industry')}
                placeholder="ej. Tecnología, Manufactura, Servicios"
              />
            </Grid>
            
            <Grid item xs={12} sm={4}>
              <TextField
                fullWidth
                label="Ingresos Anuales"
                type="number"
                value={formData.annual_revenue}
                onChange={handleChange('annual_revenue')}
                InputProps={{
                  startAdornment: <InputAdornment position="start">S/</InputAdornment>,
                }}
              />
            </Grid>
            
            <Grid item xs={12} sm={4}>
              <TextField
                fullWidth
                label="Número de Empleados"
                type="number"
                value={formData.employees}
                onChange={handleChange('employees')}
              />
            </Grid>
            
            <Grid item xs={12} sm={4}>
              <TextField
                fullWidth
                label="Sitio Web"
                value={formData.website}
                onChange={handleChange('website')}
                placeholder="empresa.com"
              />
            </Grid>
          </Grid>

          <Divider sx={{ my: 2 }} />
          
          <Typography variant="h6" gutterBottom>
            Información de Contacto
          </Typography>
          
          <Grid container spacing={2} sx={{ mb: 3 }}>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Teléfono Principal"
                value={formData.phone}
                onChange={handleChange('phone')}
              />
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Email Principal"
                type="email"
                value={formData.email}
                onChange={handleChange('email')}
                error={!!errors.email}
                helperText={errors.email}
              />
            </Grid>
          </Grid>

          <Divider sx={{ my: 2 }} />
          
          <Typography variant="h6" gutterBottom>
            Dirección de Facturación
          </Typography>
          
          <Grid container spacing={2} sx={{ mb: 3 }}>
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Dirección"
                value={formData.billing_street}
                onChange={handleChange('billing_street')}
                multiline
                rows={2}
              />
            </Grid>
            
            <Grid item xs={12} sm={4}>
              <TextField
                fullWidth
                label="Ciudad"
                value={formData.billing_city}
                onChange={handleChange('billing_city')}
              />
            </Grid>
            
            <Grid item xs={12} sm={4}>
              <TextField
                fullWidth
                label="Estado/Región"
                value={formData.billing_state}
                onChange={handleChange('billing_state')}
              />
            </Grid>
            
            <Grid item xs={12} sm={4}>
              <TextField
                fullWidth
                label="País"
                value={formData.billing_country}
                onChange={handleChange('billing_country')}
              />
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Código Postal"
                value={formData.billing_postal_code}
                onChange={handleChange('billing_postal_code')}
              />
            </Grid>
          </Grid>

          <Divider sx={{ my: 2 }} />
          
          <Box display="flex" justifyContent="space-between" alignItems="center" mb={2}>
            <Typography variant="h6">
              Dirección de Envío
            </Typography>
            <Button
              variant="outlined"
              size="small"
              onClick={copyBillingToShipping}
            >
              Copiar de Facturación
            </Button>
          </Box>
          
          <Grid container spacing={2} sx={{ mb: 3 }}>
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Dirección de Envío"
                value={formData.shipping_street}
                onChange={handleChange('shipping_street')}
                multiline
                rows={2}
              />
            </Grid>
            
            <Grid item xs={12} sm={4}>
              <TextField
                fullWidth
                label="Ciudad"
                value={formData.shipping_city}
                onChange={handleChange('shipping_city')}
              />
            </Grid>
            
            <Grid item xs={12} sm={4}>
              <TextField
                fullWidth
                label="Estado/Región"
                value={formData.shipping_state}
                onChange={handleChange('shipping_state')}
              />
            </Grid>
            
            <Grid item xs={12} sm={4}>
              <TextField
                fullWidth
                label="País"
                value={formData.shipping_country}
                onChange={handleChange('shipping_country')}
              />
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Código Postal"
                value={formData.shipping_postal_code}
                onChange={handleChange('shipping_postal_code')}
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
            placeholder="Información adicional sobre la cuenta..."
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
          {loading ? 'Guardando...' : account ? 'Actualizar' : 'Crear'}
        </Button>
      </DialogActions>
    </Dialog>
  );
}

export default AccountForm;