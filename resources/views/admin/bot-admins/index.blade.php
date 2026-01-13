@extends('layouts.app')

@section('title', 'Bot Admin Management')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1">Bot Admin Management</h2>
            <p class="text-muted mb-0">Kelola admin dan moderator bot Telegram</p>
        </div>
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#permissionMatrixModal">
            <i class="fas fa-info-circle"></i> Permission Matrix
        </button>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4" id="stats-cards">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Users</p>
                            <h4 class="mb-0" id="stat-total-users">-</h4>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-users fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Admins</p>
                            <h4 class="mb-0 text-danger" id="stat-total-admins">-</h4>
                        </div>
                        <div class="text-danger">
                            <i class="fas fa-user-shield fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Moderators</p>
                            <h4 class="mb-0 text-warning" id="stat-total-moderators">-</h4>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-user-cog fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">VIP Users</p>
                            <h4 class="mb-0 text-success" id="stat-total-vip">-</h4>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-crown fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">Telegram Users</h5>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" id="search-input" placeholder="Search by username, name, telegram ID...">
                        <button class="btn btn-outline-secondary" type="button" id="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="users-table">
                    <thead class="table-light">
                        <tr>
                            <th>Telegram ID</th>
                            <th>User Info</th>
                            <th>Role</th>
                            <th>VIP Status</th>
                            <th>Linked Web User</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-tbody">
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small" id="pagination-info">
                    Showing 0 to 0 of 0 entries
                </div>
                <nav id="pagination-nav"></nav>
            </div>
        </div>
    </div>
</div>

<!-- Permission Matrix Modal -->
<div class="modal fade" id="permissionMatrixModal" tabindex="-1" aria-labelledby="permissionMatrixModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permissionMatrixModalLabel">
                    <i class="fas fa-shield-alt"></i> Permission Matrix
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Permission</th>
                                <th class="text-center"><span class="badge bg-secondary">User</span></th>
                                <th class="text-center"><span class="badge bg-warning">Moderator</span></th>
                                <th class="text-center"><span class="badge bg-danger">Admin</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><i class="fas fa-eye text-muted"></i> View Movies</td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-plus text-muted"></i> Add Movies</td>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-edit text-muted"></i> Edit Movies</td>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-trash text-muted"></i> Delete Movies</td>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-crown text-muted"></i> Manage VIP</td>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-users text-muted"></i> Manage Users</td>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-credit-card text-muted"></i> Manage Payments</td>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-chart-bar text-muted"></i> View Analytics</td>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mb-0">
                    <strong><i class="fas fa-info-circle"></i> Note:</strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>User:</strong> Regular user, can only view movies</li>
                        <li><strong>Moderator:</strong> Can add new movies via bot, but cannot edit, delete, or manage other features</li>
                        <li><strong>Admin:</strong> Full access to all features including user management, VIP management, and payments</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Role Change Modal -->
<div class="modal fade" id="roleChangeModal" tabindex="-1" aria-labelledby="roleChangeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleChangeModalLabel">Change User Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Select new role for <strong id="roleChangeUserName"></strong>:</p>
                <input type="hidden" id="roleChangeUserId">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="newRole" id="roleUser" value="user">
                    <label class="form-check-label" for="roleUser">
                        <span class="badge bg-secondary">User</span>
                        <small class="text-muted d-block">Can only view movies</small>
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="newRole" id="roleModerator" value="moderator">
                    <label class="form-check-label" for="roleModerator">
                        <span class="badge bg-warning">Moderator</span>
                        <small class="text-muted d-block">Can add movies only</small>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="newRole" id="roleAdmin" value="admin">
                    <label class="form-check-label" for="roleAdmin">
                        <span class="badge bg-danger">Admin</span>
                        <small class="text-muted d-block">Full access to all features</small>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmRoleChange()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentPage = 1;
let currentQuery = '';
let currentPerPage = 10;

// Load statistics
function loadStats() {
    fetch('/bot-admins/stats')
        .then(response => response.json())
        .then(data => {
            document.getElementById('stat-total-users').textContent = data.total_users;
            document.getElementById('stat-total-admins').textContent = data.total_admins;
            document.getElementById('stat-total-moderators').textContent = data.total_moderators;
            document.getElementById('stat-total-vip').textContent = data.total_vip;
        })
        .catch(error => console.error('Error loading stats:', error));
}

// Load users data
function loadUsers(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('users-tbody');

    tbody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </td>
        </tr>
    `;

    const params = new URLSearchParams({
        page: page,
        per_page: currentPerPage,
        q: currentQuery
    });

    fetch(`/bot-admins/data?${params}`)
        .then(response => response.json())
        .then(data => {
            renderUsersTable(data);
            renderPagination(data);
            loadStats(); // Refresh stats
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-danger py-4">
                        Error loading data. Please try again.
                    </td>
                </tr>
            `;
        });
}

// Render users table
function renderUsersTable(data) {
    const tbody = document.getElementById('users-tbody');

    if (data.data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    No users found
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = data.data.map(user => `
        <tr>
            <td>
                <code class="small">${user.telegram_user_id}</code>
            </td>
            <td>
                <div>
                    <strong>${user.full_name || 'N/A'}</strong>
                    ${user.username ? `<br><small class="text-muted">@${user.username}</small>` : ''}
                </div>
            </td>
            <td>
                <span class="badge ${getRoleBadgeClass(user.role)}">
                    ${user.role_display}
                </span>
            </td>
            <td>
                ${user.is_vip
                    ? `<span class="badge bg-success"><i class="fas fa-crown"></i> VIP</span><br><small class="text-muted">${user.vip_until}</small>`
                    : '<span class="badge bg-secondary">Regular</span>'
                }
            </td>
            <td>
                ${user.linked_user_name
                    ? `<div><strong>${user.linked_user_name}</strong><br><small class="text-muted">${user.linked_user_email}</small></div>`
                    : '<span class="text-muted">Not linked</span>'
                }
            </td>
            <td>
                <small class="text-muted">${user.created_at}</small>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="setRole(${user.id}, '${user.role}')" title="Change Role">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-outline-${user.role === 'admin' ? 'danger' : 'success'}"
                            onclick="toggleAdmin(${user.id}, '${user.role}')"
                            title="${user.role === 'admin' ? 'Demote' : 'Promote'} to Admin">
                        <i class="fas fa-${user.role === 'admin' ? 'user-minus' : 'user-plus'}"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Get badge class for role
function getRoleBadgeClass(role) {
    switch(role) {
        case 'admin': return 'bg-danger';
        case 'moderator': return 'bg-warning';
        default: return 'bg-secondary';
    }
}

// Render pagination
function renderPagination(data) {
    const nav = document.getElementById('pagination-nav');
    const info = document.getElementById('pagination-info');

    info.textContent = `Showing ${data.from || 0} to ${data.to || 0} of ${data.total} entries`;

    if (data.last_page <= 1) {
        nav.innerHTML = '';
        return;
    }

    let pages = '';
    for (let i = 1; i <= data.last_page; i++) {
        if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
            pages += `
                <li class="page-item ${i === data.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadUsers(${i}); return false;">${i}</a>
                </li>
            `;
        } else if (i === data.current_page - 3 || i === data.current_page + 3) {
            pages += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    nav.innerHTML = `
        <ul class="pagination mb-0">
            <li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadUsers(${data.current_page - 1}); return false;">Previous</a>
            </li>
            ${pages}
            <li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadUsers(${data.current_page + 1}); return false;">Next</a>
            </li>
        </ul>
    `;
}

// Toggle admin status
function toggleAdmin(userId, currentRole) {
    const action = currentRole === 'admin' ? 'demote' : 'promote';

    if (!confirm(`Are you sure you want to ${action} this user?`)) {
        return;
    }

    fetch(`/bot-admins/${userId}/toggle-admin`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            loadUsers(currentPage);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred. Please try again.');
    });
}

// Set role - open modal
function setRole(userId, currentRole) {
    // Get user info from the table
    const row = event.target.closest('tr');
    const userName = row.querySelector('td:nth-child(2) strong').textContent;

    // Set modal data
    document.getElementById('roleChangeUserId').value = userId;
    document.getElementById('roleChangeUserName').textContent = userName;

    // Set current role as checked
    document.getElementById('roleUser').checked = (currentRole === 'user');
    document.getElementById('roleModerator').checked = (currentRole === 'moderator');
    document.getElementById('roleAdmin').checked = (currentRole === 'admin');

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('roleChangeModal'));
    modal.show();
}

// Confirm role change from modal
function confirmRoleChange() {
    const userId = document.getElementById('roleChangeUserId').value;
    const selectedRole = document.querySelector('input[name="newRole"]:checked');

    if (!selectedRole) {
        showAlert('warning', 'Please select a role.');
        return;
    }

    fetch(`/bot-admins/${userId}/set-role`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ role: selectedRole.value })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            loadUsers(currentPage);
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('roleChangeModal'));
            modal.hide();
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred. Please try again.');
    });
}

// Show alert
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);

    setTimeout(() => alertDiv.remove(), 5000);
}

// Search functionality
document.getElementById('search-btn').addEventListener('click', () => {
    currentQuery = document.getElementById('search-input').value;
    loadUsers(1);
});

document.getElementById('search-input').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        currentQuery = e.target.value;
        loadUsers(1);
    }
});

// Initial load
document.addEventListener('DOMContentLoaded', () => {
    loadStats();
    loadUsers(1);
});
</script>
@endpush
@endsection
