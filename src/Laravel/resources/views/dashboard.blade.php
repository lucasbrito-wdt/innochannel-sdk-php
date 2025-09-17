@extends('layouts.app')

@section('title', 'Innochannel Dashboard')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('innochannel::messages.dashboard.title', ['default' => 'Innochannel Dashboard']) }}</h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card text-white bg-primary mb-3">
                                    <div class="card-header">Reservations</div>
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $stats['bookings'] ?? 0 }}</h5>
                                        <p class="card-text">Total bookings</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card text-white bg-success mb-3">
                                    <div class="card-header">Properties</div>
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $stats['properties'] ?? 0 }}</h5>
                                        <p class="card-text">Active properties</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card text-white bg-info mb-3">
                                    <div class="card-header">Inventory</div>
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $stats['inventory_updates'] ?? 0 }}</h5>
                                        <p class="card-text">Recent updates</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card text-white bg-warning mb-3">
                                    <div class="card-header">Webhooks</div>
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $stats['webhooks'] ?? 0 }}</h5>
                                        <p class="card-text">Processed today</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5>Recent Activity</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($activities ?? [] as $activity)
                                                <tr>
                                                    <td>{{ $activity['type'] }}</td>
                                                    <td>{{ $activity['description'] }}</td>
                                                    <td>
                                                        <span
                                                            class="badge badge-{{ $activity['status'] === 'success' ? 'success' : 'danger' }}">
                                                            {{ $activity['status'] }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $activity['created_at'] }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">No recent activity</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
