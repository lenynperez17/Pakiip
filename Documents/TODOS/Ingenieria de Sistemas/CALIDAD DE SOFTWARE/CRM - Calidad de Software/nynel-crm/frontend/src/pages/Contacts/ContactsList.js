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
  Tabs,
  Tab,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  TablePagination,
  InputAdornment,
  Fab,
  Tooltip
} from '@mui/material';
import {
  Add as AddIcon,
  Search as SearchIcon,
  FilterList as FilterIcon,
  MoreVert as MoreVertIcon,
  Person as PersonIcon,
  Business as BusinessIcon,
  Email as EmailIcon,
  Phone as PhoneIcon,
  Edit as EditIcon,
  Delete as DeleteIcon,
  Visibility as ViewIcon
} from '@mui/icons-material';
import { contactsAPI } from '../../services/api';
import ContactForm from '../../components/Forms/ContactForm';
import AccountForm from '../../components/Forms/AccountForm';

function ContactsList() {
  
  const [contacts, setContacts] = useState([]);
  const [accounts, setAccounts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedTab, setSelectedTab] = useState(0);
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(10);
  const [anchorEl, setAnchorEl] = useState(null);
  const [selectedItem, setSelectedItem] = useState(null);
  const [openDialog, setOpenDialog] = useState(false);
  const [dialogType, setDialogType] = useState(''); // 'contact', 'account', 'view', 'delete'

  useEffect(() => {
    loadData();
  }, [selectedTab, searchTerm, page, rowsPerPage]);

  const loadData = async () => {
    try {
      setLoading(true);
      if (selectedTab === 0) {
        const params = {
          search: searchTerm,
          page: page + 1,
          page_size: rowsPerPage
        };
        const response = await contactsAPI.getContacts(params);
        setContacts(response.data.results || response.data);
      } else {
        const params = {
          search: searchTerm,
          page: page + 1,
          page_size: rowsPerPage
        };
        const response = await contactsAPI.getAccounts(params);
        setAccounts(response.data.results || response.data);
      }
    } catch (error) {
      console.error('Error loading data:', error);
    } finally {
      setLoading(false);
    }
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
        await contactsAPI.deleteContact(selectedItem.id);
      } else {
        await contactsAPI.deleteAccount(selectedItem.id);
      }
      loadData();
      handleDialogClose();
    } catch (error) {
      console.error('Error deleting item:', error);
    }
  };

  const ContactCard = ({ contact }) => (
    <Card sx={{ mb: 2 }}>
      <CardContent>
        <Grid container spacing={2} alignItems="center">
          <Grid item>
            <Avatar sx={{ bgcolor: 'primary.main' }}>
              <PersonIcon />
            </Avatar>
          </Grid>
          <Grid item xs>
            <Typography variant="h6">
              {contact.first_name} {contact.last_name}
            </Typography>
            <Typography variant="body2" color="textSecondary">
              {contact.job_title} {contact.account_name && `at ${contact.account_name}`}
            </Typography>
            <Box display="flex" gap={1} mt={1}>
              <Chip 
                icon={<EmailIcon />} 
                label={contact.email} 
                size="small" 
                variant="outlined" 
              />
              {contact.phone && (
                <Chip 
                  icon={<PhoneIcon />} 
                  label={contact.phone} 
                  size="small" 
                  variant="outlined" 
                />
              )}
            </Box>
          </Grid>
          <Grid item>
            <IconButton onClick={(e) => handleMenuClick(e, contact)}>
              <MoreVertIcon />
            </IconButton>
          </Grid>
        </Grid>
      </CardContent>
    </Card>
  );

  const AccountCard = ({ account }) => (
    <Card sx={{ mb: 2 }}>
      <CardContent>
        <Grid container spacing={2} alignItems="center">
          <Grid item>
            <Avatar sx={{ bgcolor: 'secondary.main' }}>
              <BusinessIcon />
            </Avatar>
          </Grid>
          <Grid item xs>
            <Typography variant="h6">{account.name}</Typography>
            <Typography variant="body2" color="textSecondary">
              {account.industry} • {account.account_type}
            </Typography>
            <Box display="flex" gap={1} mt={1}>
              <Chip 
                label={`${account.contacts_count || 0} contactos`}
                size="small" 
                color="primary"
                variant="outlined" 
              />
              {account.website && (
                <Chip 
                  label="Sitio web" 
                  size="small" 
                  variant="outlined" 
                />
              )}
            </Box>
          </Grid>
          <Grid item>
            <IconButton onClick={(e) => handleMenuClick(e, account)}>
              <MoreVertIcon />
            </IconButton>
          </Grid>
        </Grid>
      </CardContent>
    </Card>
  );

  const ContactsTable = () => (
    <TableContainer component={Paper}>
      <Table>
        <TableHead>
          <TableRow>
            <TableCell>Nombre</TableCell>
            <TableCell>Email</TableCell>
            <TableCell>Teléfono</TableCell>
            <TableCell>Empresa</TableCell>
            <TableCell>Cargo</TableCell>
            <TableCell align="right">Acciones</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {contacts.map((contact) => (
            <TableRow key={contact.id} hover>
              <TableCell>
                <Box display="flex" alignItems="center" gap={1}>
                  <Avatar sx={{ width: 32, height: 32 }}>
                    <PersonIcon />
                  </Avatar>
                  {contact.first_name} {contact.last_name}
                </Box>
              </TableCell>
              <TableCell>{contact.email}</TableCell>
              <TableCell>{contact.phone || '-'}</TableCell>
              <TableCell>{contact.account_name || '-'}</TableCell>
              <TableCell>{contact.job_title || '-'}</TableCell>
              <TableCell align="right">
                <IconButton 
                  size="small" 
                  onClick={(e) => handleMenuClick(e, contact)}
                >
                  <MoreVertIcon />
                </IconButton>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </TableContainer>
  );

  const AccountsTable = () => (
    <TableContainer component={Paper}>
      <Table>
        <TableHead>
          <TableRow>
            <TableCell>Empresa</TableCell>
            <TableCell>Tipo</TableCell>
            <TableCell>Industria</TableCell>
            <TableCell>Contactos</TableCell>
            <TableCell>RUC</TableCell>
            <TableCell align="right">Acciones</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {accounts.map((account) => (
            <TableRow key={account.id} hover>
              <TableCell>
                <Box display="flex" alignItems="center" gap={1}>
                  <Avatar sx={{ width: 32, height: 32 }}>
                    <BusinessIcon />
                  </Avatar>
                  {account.name}
                </Box>
              </TableCell>
              <TableCell>
                <Chip 
                  label={account.account_type} 
                  size="small" 
                  color="primary"
                  variant="outlined" 
                />
              </TableCell>
              <TableCell>{account.industry || '-'}</TableCell>
              <TableCell>{account.contacts_count || 0}</TableCell>
              <TableCell>{account.ruc}</TableCell>
              <TableCell align="right">
                <IconButton 
                  size="small" 
                  onClick={(e) => handleMenuClick(e, account)}
                >
                  <MoreVertIcon />
                </IconButton>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </TableContainer>
  );

  return (
    <Box>
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
        <Typography variant="h4" fontWeight="bold">
          {selectedTab === 0 ? 'Contactos' : 'Cuentas'}
        </Typography>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={() => handleDialogOpen(selectedTab === 0 ? 'contact' : 'account')}
        >
          {selectedTab === 0 ? 'Nuevo Contacto' : 'Nueva Cuenta'}
        </Button>
      </Box>

      <Paper sx={{ mb: 3 }}>
        <Tabs 
          value={selectedTab} 
          onChange={(e, newValue) => setSelectedTab(newValue)}
          sx={{ borderBottom: 1, borderColor: 'divider' }}
        >
          <Tab label="Contactos" />
          <Tab label="Cuentas" />
        </Tabs>
        
        <Box p={2}>
          <TextField
            fullWidth
            placeholder={`Buscar ${selectedTab === 0 ? 'contactos' : 'cuentas'}...`}
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <SearchIcon />
                </InputAdornment>
              ),
            }}
            sx={{ mb: 2 }}
          />
        </Box>
      </Paper>

      {loading ? (
        <Box display="flex" justifyContent="center" p={4}>
          <Typography>Cargando...</Typography>
        </Box>
      ) : (
        <>
          {selectedTab === 0 ? <ContactsTable /> : <AccountsTable />}
          
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
        <MenuItem onClick={() => handleDialogOpen(selectedTab === 0 ? 'edit' : 'edit')}>
          <EditIcon sx={{ mr: 1 }} />
          Editar
        </MenuItem>
        <MenuItem onClick={() => handleDialogOpen('delete')}>
          <DeleteIcon sx={{ mr: 1 }} />
          Eliminar
        </MenuItem>
      </Menu>

      {/* Contact Form Dialog */}
      <ContactForm
        open={openDialog && selectedTab === 0 && (dialogType === 'contact' || dialogType === 'edit')}
        onClose={handleDialogClose}
        contact={dialogType === 'edit' ? selectedItem : null}
        onSave={loadData}
      />

      {/* Account Form Dialog */}
      <AccountForm
        open={openDialog && selectedTab === 1 && (dialogType === 'account' || dialogType === 'edit')}
        onClose={handleDialogClose}
        account={dialogType === 'edit' ? selectedItem : null}
        onSave={loadData}
      />

      {/* Diálogo de confirmación de eliminación */}
      <Dialog open={openDialog && dialogType === 'delete'} onClose={handleDialogClose}>
        <DialogTitle>Confirmar eliminación</DialogTitle>
        <DialogContent>
          <Typography>
            ¿Estás seguro de que deseas eliminar este {selectedTab === 0 ? 'contacto' : 'cuenta'}?
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
        onClick={() => handleDialogOpen(selectedTab === 0 ? 'contact' : 'account')}
      >
        <AddIcon />
      </Fab>
    </Box>
  );
}

export default ContactsList;