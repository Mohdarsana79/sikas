@extends('layouts.app')
@include('layouts.sidebar')
@include('layouts.navbar')
@section('content')
    <div class="content-area" id="contentArea">
        <!-- Dashboard Content -->
        <div class="fade-in">
            <!-- Page Title -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1 text-gradient">Dashboard</h1>
                    <p class="text-muted">Selamat datang kembali! Berikut adalah ringkasan aktivitas hari ini.</p>
                </div>
                <div>
                    <button class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Baru
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon primary">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stats-value">2,543</div>
                        <div class="stats-label">Total Users</div>
                        <div class="stats-change positive">
                            <i class="bi bi-arrow-up"></i> +12.5%
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon success">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <div class="stats-value">$45,210</div>
                        <div class="stats-label">Revenue</div>
                        <div class="stats-change positive">
                            <i class="bi bi-arrow-up"></i> +8.2%
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon warning">
                            <i class="bi bi-cart"></i>
                        </div>
                        <div class="stats-value">1,234</div>
                        <div class="stats-label">Orders</div>
                        <div class="stats-change negative">
                            <i class="bi bi-arrow-down"></i> -3.1%
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon info">
                            <i class="bi bi-eye"></i>
                        </div>
                        <div class="stats-value">98,765</div>
                        <div class="stats-label">Page Views</div>
                        <div class="stats-change positive">
                            <i class="bi bi-arrow-up"></i> +15.3%
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row g-4 mb-4">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <h5>Revenue Overview</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueChart" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Traffic Sources</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="trafficChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row g-4">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <h5>Recent Orders</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Product</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>#ORD-001</td>
                                            <td>John Doe</td>
                                            <td>MacBook Pro</td>
                                            <td>$2,499</td>
                                            <td><span class="badge bg-success">Completed</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">View</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#ORD-002</td>
                                            <td>Jane Smith</td>
                                            <td>iPhone 15</td>
                                            <td>$999</td>
                                            <td><span class="badge bg-warning">Pending</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">View</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#ORD-003</td>
                                            <td>Mike Johnson</td>
                                            <td>iPad Air</td>
                                            <td>$599</td>
                                            <td><span class="badge bg-info">Processing</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">View</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#ORD-004</td>
                                            <td>Sarah Wilson</td>
                                            <td>AirPods Pro</td>
                                            <td>$249</td>
                                            <td><span class="badge bg-success">Completed</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">View</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <div class="activity-item d-flex align-items-start mb-3">
                                <div class="activity-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 32px; height: 32px;">
                                    <i class="bi bi-person-plus"></i>
                                </div>
                                <div>
                                    <div class="fw-medium">New user registered</div>
                                    <div class="text-muted small">2 minutes ago</div>
                                </div>
                            </div>

                            <div class="activity-item d-flex align-items-start mb-3">
                                <div class="activity-icon bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 32px; height: 32px;">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <div>
                                    <div class="fw-medium">Order completed</div>
                                    <div class="text-muted small">5 minutes ago</div>
                                </div>
                            </div>

                            <div class="activity-item d-flex align-items-start mb-3">
                                <div class="activity-icon bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 32px; height: 32px;">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </div>
                                <div>
                                    <div class="fw-medium">Server maintenance</div>
                                    <div class="text-muted small">1 hour ago</div>
                                </div>
                            </div>

                            <div class="activity-item d-flex align-items-start">
                                <div class="activity-icon bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 32px; height: 32px;">
                                    <i class="bi bi-upload"></i>
                                </div>
                                <div>
                                    <div class="fw-medium">Database backup</div>
                                    <div class="text-muted small">3 hours ago</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
