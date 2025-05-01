# QA Checklist for Help Desk System

## Database Integrity
- [ ] Migrations run without errors
  - [ ] `php artisan migrate:fresh` completes successfully
  - [ ] Database structure matches expected schema
- [ ] Seeders run without errors
  - [ ] `php artisan db:seed` completes successfully
  - [ ] Test data is properly populated
  - [ ] User roles are correctly assigned (super_admin, admin, agent, user)

## Role Permissions & Visibility
- [ ] Super Admin & Admin
  - [ ] Can view all tickets regardless of department or ownership
  - [ ] Can update any ticket
  - [ ] Can delete and restore tickets
- [ ] Agent
  - [ ] Can only view tickets assigned to them
  - [ ] Can only view tickets in their department
  - [ ] Cannot delete or restore tickets
- [ ] Regular User
  - [ ] Can only view their own tickets
  - [ ] Cannot delete or restore tickets
- [ ] Interface elements correctly hidden/shown based on user role

## Responsive Design
- [ ] Mobile layout works correctly (inspect at ≤425px width)
  - [ ] Table rows stack properly on mobile view
  - [ ] All text and form inputs are properly sized
  - [ ] Navigation is accessible on small screens
  - [ ] No horizontal scrolling required
  - [ ] Interactive elements have proper touch targets

## Ticket Functionality
- [ ] Create Ticket
  - [ ] Form validation works correctly
  - [ ] Attachments upload properly
  - [ ] New ticket appears in list after creation
  - [ ] Department assignment works
  - [ ] Priority assignment works
- [ ] Add Comment
  - [ ] Can add comments to existing tickets
  - [ ] Comments display in chronological order
  - [ ] Formatting is preserved
- [ ] Notifications
  - [ ] Email notifications fire when comments are added
  - [ ] Web push notifications work when enabled
  - [ ] Notifications contain correct ticket/comment information
  - [ ] Notification links bring user to correct ticket

## Performance
- [ ] Page load times are acceptable
- [ ] Database queries are optimized
- [ ] Images and attachments are properly optimized

## Security
- [ ] Users cannot access tickets they don't have permission for
- [ ] Form submissions validate CSRF tokens
- [ ] Input sanitization is working correctly

## General
- [ ] No console errors in browser developer tools
- [ ] No PHP errors or exceptions in logs