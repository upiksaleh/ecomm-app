# Multi-Tenant eCommerce Platform Todo List

## Core Features

### Multi-Tenant Architecture
- [x] Install stancl/tenancy
- [x] Configure central database
- [x] Configure tenant database
- [x] Setup tenant migration system
- [x] Setup tenant seeder
- [x] Configure tenant routes
- [x] Configure central routes
- [x] Setup tenant middleware
- [x] Test database isolation

### Central & Tenant Authentication
#### Central Auth
- [x] Create central User model
- [x] Setup central login
- [ ] Setup central register (optional)
- [ ] Setup email verification (optional)
- [ ] Setup password reset (optional)
- [x] Protect central dashboard

#### Tenant Auth
- [x] Create tenant User model
- [x] Configure tenant auth guard
- [x] Setup tenant login
- [ ] Setup tenant register (optional)
- [ ] Setup tenant email verification (optional)
- [ ] Setup tenant password reset (optional)
- [x] Protect tenant dashboard

### Tenant Onboarding System
- [ ] Create tenant registration flow (optional)
- [x] Create tenant database automatically
- [x] Run tenant migrations on creation
- [x] Assign tenant domain/subdomain
- [x] Create default tenant admin
- [x] Tenant onboarding validation
- [x] Redirect after tenant creation

### Product Management
- [x] Create Product model
- [x] Create Product migration
- [x] Product CRUD controller
- [x] Product form validation
- [x] Product listing page
- [x] Product edit page
- [x] Product delete feature

### Shopping Cart System
- [x] Create Cart model
- [x] Create CartItem model
- [x] Add to cart feature
- [x] Remove item feature
- [x] Update quantity feature
- [x] Calculate cart totals
- [x] Store cart in database
- [x] Cart page UI

### Tenant Frontend
- [x] Tenant dashboard UI
- [x] Product management UI
- [x] Store product listing UI
- [x] Shopping cart UI
- [x] Basic checkout UI

### Database Isolation
- [x] Dynamic tenant database switching
- [x] Ensure central never accesses tenant DB
- [x] Ensure tenant cannot access central DB
- [x] Test tenant isolation
- [x] Optimize tenant bootstrapping

---

## Technical Features

### Backend Setup
- [x] Laravel project structure cleanup
- [ ] Service layer structure (optional best practice)
- [ ] Repository pattern (optional)
- [ ] API resource responses
- [ ] Global exception handler

### Frontend Setup
- [x] Vue 3 setup with Inertia
- [x] Layout separation (Central vs Tenant)
- [x] Shared components
- [x] Form components
- [x] Flash messages
- [x] Error pages

### Multi Database Connection
- [ ] Configure tenant connection resolver
- [ ] Tenant database config template
- [ ] Handle connection switching
- [ ] Test connection switching
- [ ] Handle queue tenant context (optional)

---

## Security
- [ ] Request validation
- [ ] Authorization policies
- [ ] Role system (admin/customer)
- [ ] Prevent mass assignment issues
- [ ] Secure file upload
- [ ] XSS protection
- [ ] CSRF protection

---

## Testing
- [ ] Tenant creation test
- [ ] Tenant database test
- [ ] Auth test
- [ ] Product CRUD test
- [ ] Cart test
- [ ] Feature tests
- [ ] Isolation tests

---

## Documentation
- [ ] Update README
- [ ] Architecture diagram
- [ ] Tenant lifecycle documentation
- [ ] Installation guide
- [ ] Environment setup
- [ ] Deployment steps

---

## Deployment
- [ ] Prepare production .env
- [ ] Setup storage link
- [ ] Optimize config
- [ ] Optimize routes
- [ ] Optimize views
- [ ] Final testing
- [ ] Push to GitHub

