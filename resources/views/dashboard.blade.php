<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <title>Boards - {{ config('app.name', 'Trello') }}</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Scrollbar hiding rules for cross-browser compatibility */
        body {
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE/Edge */
        }

        body::-webkit-scrollbar {
            display: none; /* Chrome/Safari/Opera */
        }

        .sidebar {
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE/Edge */
        }

        .sidebar::-webkit-scrollbar {
            display: none; /* Chrome/Safari/Opera */
        }

        .main-content {
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE/Edge */
        }

        .main-content::-webkit-scrollbar {
            display: none; /* Chrome/Safari/Opera */
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #1d2125;
            color: #b6c2cf;
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        .top-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 40px;
            background: #1d2125;
            border-bottom: 1px solid #38414a;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 12px;
            z-index: 1000;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .grid-icon {
            width: 20px;
            height: 20px;
            color: #b6c2cf;
        }

        .trello-logo-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
        }

        .trello-logo-header:hover {
            background: #22272b;
        }

        .trello-logo-small {
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, #0052cc 0%, #0065ff 100%);
            border-radius: 6px;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .trello-logo-small::before,
        .trello-logo-small::after {
            content: '';
            width: 10px;
            height: 4px;
            background: white;
            border-radius: 1px;
        }

        .trello-logo-header:hover .trello-logo-small {
            background: linear-gradient(135deg, #0065ff 0%, #0079ff 100%);
            box-shadow: 0 3px 6px rgba(0,0,0,0.3);
        }

        .trello-text-header {
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
        }

        .search-bar {
            flex: 1;
            max-width: 400px;
            margin: 0 16px;
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            color: #8c9cb8;
        }

        .search-input {
            width: 100%;
            padding: 6px 12px 6px 36px;
            background: #22272b;
            border: 1px solid #38414a;
            border-radius: 4px;
            color: #b6c2cf;
            font-size: 14px;
        }

        .search-input:focus {
            outline: none;
            border-color: #0c66e4;
            background: #282e33;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-create {
            background: #0052cc;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-create:hover {
            background: #0065ff;
        }

        .header-icon {
            width: 32px;
            height: 32px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #b6c2cf;
        }

        .header-icon:hover {
            background: #22272b;
        }

        .user-avatar-header {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #0c66e4;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            cursor: pointer;
            position: relative;
        }

        .user-dropdown {
            position: absolute;
            top: 40px;
            right: 0;
            background: #282e33;
            border: 1px solid #38414a;
            border-radius: 4px;
            width: 320px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.4);
            display: none;
            z-index: 1001;
            padding: 8px 0;
        }

        .user-dropdown.active {
            display: block;
        }

        .dropdown-item {
            padding: 8px 16px;
            color: #b6c2cf;
            font-size: 14px;
            text-decoration: none;
            display: block;
            cursor: pointer;
        }

        .dropdown-item:hover {
            background: #323940;
            color: white;
        }

        .dropdown-divider {
            height: 1px;
            background: #38414a;
            margin: 8px 0;
        }

        .dropdown-user-info {
            padding: 8px 16px;
            font-size: 12px;
            color: #8c9cb8;
            border-bottom: 1px solid #38414a;
            margin-bottom: 8px;
            word-break: break-all;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 40px;
            bottom: 0;
            width: 282px;
            background: #22272b;
            border-right: 1px solid #38414a;
            overflow-y: auto;
            padding: 16px 8px;
            z-index: 999;
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            color: #b6c2cf;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .sidebar-item:hover {
            background: #2c333a;
        }

        .sidebar-item.active {
            background: #0c66e4;
            color: white;
        }

        .sidebar-icon {
            width: 20px;
            height: 20px;
        }

        .sidebar-section {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #38414a;
        }

        .sidebar-section-title {
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 600;
            color: #8c9cb8;
            text-transform: uppercase;
        }

        .workspace-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            color: #b6c2cf;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 2px;
            text-decoration: none;
        }

        .workspace-item:hover {
            background: #2c333a;
            color: #ffffff;
        }

        .workspace-icon {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            color: white;
            text-transform: uppercase;
            overflow: hidden;
        }

        .workspace-icon.blue {
            background: linear-gradient(135deg, #0052cc 0%, #0065ff 100%);
        }

        .workspace-icon.green {
            background: linear-gradient(135deg, #0c66e4 0%, #1e88e5 100%);
        }

        .workspace-icon.purple {
            background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%);
        }

        .workspace-icon.red {
            background: linear-gradient(135deg, #c9372c 0%, #eb5a46 100%);
        }

        .workspace-icon.orange {
            background: linear-gradient(135deg, #ff9f1a 0%, #ffab00 100%);
        }

        .main-content {
            margin-left: 282px; /* Matches sidebar width */
            margin-top: 40px;
            padding: 32px 48px; /* Maintaining the 'breathing room' padding */
            min-height: 100vh;
            background: #1d2125;
            width: calc(100vw - 282px);
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .section {
            margin-bottom: 32px;
        }

        .section-header {
            font-size: 16px;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 12px;
        }

        .boards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
        }

        .board-card {
            position: relative;
            height: 120px; /* Increased height for better look */
            border-radius: 12px; /* Smoother corners */
            padding: 16px;
            cursor: pointer;
            overflow: hidden;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            background: #282e33;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .board-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.4);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .board-card-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .board-card-gradient {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .board-card-gradient.blue {
            background: linear-gradient(135deg, #0079bf 0%, #0052cc 100%);
        }

        .board-card-title {
            position: relative;
            z-index: 1;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }

        .board-card.create {
            background: #282e33;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #b6c2cf;
        }

        .board-card.create:hover {
            background: #323940;
        }

        .board-card-wrapper {
            position: relative;
        }

        .board-menu-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 28px;
            height: 28px;
            background: rgba(0,0,0,0.3);
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .board-card-wrapper:hover .board-menu-btn {
            display: flex;
        }

        .board-menu-btn:hover {
            background: rgba(0,0,0,0.5);
        }

        /* Board Menu Dropdown */
        .board-menu-dropdown {
            position: absolute;
            top: 40px;
            right: 8px;
            background: #282e33;
            border: 1px solid #454f59;
            border-radius: 8px;
            min-width: 160px;
            box-shadow: 0 12px 24px rgba(0,0,0,0.5);
            display: none;
            z-index: 1000;
            padding: 6px 0;
            animation: fadeInScale 0.15s ease-out;
        }

        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .board-menu-dropdown.active {
            display: block;
        }

        .board-menu-item {
            width: 100%;
            padding: 8px 12px;
            background: none;
            border: none;
            color: #b6c2cf;
            font-size: 13px;
            text-align: left;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            transition: background 0.2s;
        }

        .board-menu-item:hover {
            background: #323940;
            color: white;
        }

        .board-menu-item svg {
            width: 14px;
            height: 14px;
            opacity: 0.7;
        }

        .board-menu-item.danger:hover {
            background: #442222;
            color: #ff5252;
        }

        /* Workspace Header Actions Upgrade */
        .workspace-action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 4px;
            background: transparent;
            border: 1px solid #38414a !important;
            color: #9fadbc;
            transition: all 0.2s;
        }

        .workspace-action-btn:hover {
            background: #2c333a;
            color: white;
            border-color: #454f59 !important;
        }

        .workspace-action-btn.delete:hover {
            background: #442222;
            color: #ff5252;
            border-color: #ff5252 !important;
        }

        /* Admin Sidebar Improvements */
        .sidebar-item-admin {
            margin-top: 4px;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }
        .sidebar-item-admin:hover {
            border-left-color: #579dff;
            background: #2c333a;
        }
        .sidebar-section-title {
            margin-top: 24px;
            margin-bottom: 8px;
            color: #9fadbc;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: #282e33;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 16px 20px;
            border-bottom: 1px solid #38414a;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: #ffffff;
        }

        .modal-close {
            background: none;
            border: none;
            color: #8c9cb8;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
        }

        .modal-close:hover {
            background: #323940;
            color: white;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #b6c2cf;
            font-size: 14px;
            font-weight: 500;
        }

        .modal-input {
            width: 100%;
            padding: 10px 12px;
            background: #22272b;
            border: 1px solid #38414a;
            border-radius: 4px;
            color: #b6c2cf;
            font-size: 14px;
        }

        .modal-input:focus {
            outline: none;
            border-color: #0c66e4;
        }

        .modal-footer {
            padding: 16px 20px;
            border-top: 1px solid #38414a;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn-secondary {
            background: #323940;
            color: #b6c2cf;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-secondary:hover {
            background: #3c444d;
        }

        .user-mgmt-container {
            display: none;
        }

        .user-mgmt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .workspace-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 8px 12px;
            background: #22272b;
            border-radius: 6px;
            border: 1px solid #38414a;
        }

        .workspace-header-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            color: #b6c2cf;
        }

        .workspace-actions {
            display: flex;
            gap: 8px;
        }



        /* Background Picker Styling */
        .bg-picker-label {
            display: block;
            margin-bottom: 12px;
            font-size: 14px;
            font-weight: 600;
            color: #b6c2cf;
        }
        .bg-options-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 20px;
        }
        .bg-option {
            height: 60px;
            border-radius: 4px;
            cursor: pointer;
            border: 2px solid transparent;
            background-size: cover;
            background-position: center;
            transition: all 0.2s;
            position: relative;
        }
        .bg-option:hover {
            opacity: 0.8;
        }
        .bg-option.active {
            border-color: #0c66e4;
        }
        .bg-option.active::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-weight: bold;
            text-shadow: 0 0 4px rgba(0,0,0,0.5);
        }
        .board-preview-card {
            width: 100%;
            height: 120px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-size: cover;
            background-position: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .board-preview-card::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.15);
        }
        .board-preview-title {
            position: relative;
            color: white;
            font-weight: 700;
            font-size: 18px;
            z-index: 1;
        }        /* Beyond Trello: Ultra Premium User Management */
        .user-mgmt-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            display: none;
            animation: slideUpFade 0.4s ease-out;
        }

        @keyframes slideUpFade {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .admin-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 32px;
            padding-bottom: 16px;
            border-bottom: 1px solid #38414a;
        }

        .admin-stat-pills {
            display: flex;
            gap: 12px;
        }

        .stat-pill {
            padding: 6px 16px;
            background: #22272b;
            border: 1px solid #38414a;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            color: #9fadbc;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .stat-pill .count {
            color: white;
            background: #0c66e4;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
        }

        .user-list-modern {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .user-row-card {
            background: #22272b;
            border: 1px solid #38414a;
            border-radius: 12px;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .user-row-card:hover {
            border-color: #454f59;
            background: #282e33;
            transform: translateX(4px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .user-info-main {
            display: flex;
            align-items: center;
            gap: 20px;
            flex: 1;
            min-width: 0;
        }

        .user-avatar-premium {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #0c66e4 0%, #0055cc 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            color: white;
            box-shadow: 0 4px 12px rgba(12, 102, 228, 0.3);
        }

        .user-meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .user-name {
            font-size: 16px;
            font-weight: 600;
            color: #b6c2cf;
        }

        .user-email {
            font-size: 13px;
            color: #8c9cb8;
        }

        .user-badge-group {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 0 0 260px; /* narrowed slightly */
            justify-content: flex-start;
        }

        .role-tag {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            color: #579dff;
            background: rgba(87, 157, 255, 0.1);
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px solid rgba(87, 157, 255, 0.2);
            width: 70px;
            flex-shrink: 0;
            text-align: center;
            letter-spacing: 0.5px;
        }

        .role-tag.user {
            color: #8c9cb8;
            background: rgba(140, 156, 184, 0.1);
            border-color: rgba(140, 156, 184, 0.2);
        }

        .status-dot {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            font-weight: 500;
            line-height: 1;
            min-width: 130px;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .dot-active { background: #4bce97; box-shadow: 0 0 10px rgba(75, 206, 151, 0.4); }
        .dot-pending { background: #f79239; box-shadow: 0 0 10px rgba(247, 146, 57, 0.4); }

        .premium-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex: 0 0 300px;
            justify-content: flex-end;
            min-height: 42px;
        }

        .btn-premium {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-p-grant {
            background: #0c66e4;
            color: white;
            box-shadow: 0 4px 12px rgba(12, 102, 228, 0.2);
        }

        .btn-p-grant:hover {
            background: #0055cc;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(12, 102, 228, 0.3);
        }

        .btn-p-danger {
            background: rgba(201, 55, 44, 0.1);
            color: #ef5c48;
            border: 1px solid rgba(201, 55, 44, 0.2);
        }

        .btn-p-danger:hover {
            background: #c9372c;
            color: white;
            transform: translateY(-2px);
        }
        .role-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .role-badge.admin {
            background: #0c66e4;
            color: white;
        }

        .role-badge.user {
            background: #323940;
            color: #b6c2cf;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.active {
            background: #1c2b1d;
            color: #4caf50;
        }

        .status-badge.inactive {
            background: #2b1c1c;
            color: #ef5350;
        }

        .user-action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .user-action-btn.activate {
            background: #1c2b1d;
            color: #4caf50;
        }

        .user-action-btn.deactivate {
            background: #2b1c1c;
            color: #ef5350;
        }

        .archived-boards-content {
            display: none;
            padding: 16px;
        }

        .archived-board-wrapper {
            position: relative;
        }

        .archived-board-card {
            opacity: 0.7;
            position: relative;
        }

        .archived-board-card::before {
            content: 'ARCHIVED';
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(0,0,0,0.6);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            z-index: 2;
        }

        .archived-board-actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }

        .archived-btn {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .archived-btn.restore-btn {
            background: #1c2b1d;
            color: #4caf50;
        }

        .archived-btn.restore-btn:hover {
            background: #2e7d32;
        }

        .archived-btn.delete-btn {
            background: #2b1c1c;
            color: #ef5350;
        }

        .archived-btn.delete-btn:hover {
            background: #c62828;
        }

        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: #8c9cb8;
        }

        .empty-state svg {
            opacity: 0.5;
            margin-bottom: 16px;
        }

        .empty-state-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .empty-state-text {
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="top-header">
        <div class="header-left">
            <svg class="grid-icon" viewBox="0 0 16 16" fill="currentColor">
                <path d="M2 2h4v4H2V2zm6 0h4v4H8V2z m6 0h4v4h-4V2zM2 8h4v4H2V8zm6 0h4v4H8V8zm6 0h4v4h-4V8zM2 14h4v4H2v-4zm6 0h4v4H8v-4zm6 0h4v4h-4v-4z"/>
            </svg>
            <div class="trello-logo-header">    
                <div class="trello-logo-small"></div>
                <span class="trello-text-header">Trello</span>
            </div>
        </div>
        <div class="search-bar" style="position:relative;">
            <svg class="search-icon" viewBox="0 0 16 16" fill="currentColor">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
            </svg>
            <input type="text" id="dashBoardSearch" class="search-input" placeholder="Search boards..." oninput="filterDashBoards()" autocomplete="off">
            <div id="dashSearchDropdown" style="display:none;position:fixed;background:#22272b;border:1px solid #454f59;border-radius:4px;z-index:9999;max-height:400px;overflow-y:auto;box-shadow:0 8px 24px rgba(0,0,0,0.5);min-width:300px;"></div>
        </div>
        <div class="header-right">
            <button type="button" class="btn-create" style="background: #0052cc; margin-right: 8px;" onclick="openClientModal()">Add Client</button>
            @if($user->isSystemAdmin() || (is_array($canCreateBoardWorkspaceIds) && count($canCreateBoardWorkspaceIds) > 0))
                <button type="button" class="btn-create" onclick="openCreateBoardModal()">Create</button> 
            @endif
            @include('partials.notification-bell')
            <div class="user-avatar-header" onclick="toggleUserDropdown()">
                {{ strtoupper(substr($user->name, 0, 2)) }}
                <div class="user-dropdown" id="userDropdown">
                    <div class="dropdown-user-info">
                        <div style="font-weight: 600; color: #b6c2cf; margin-bottom: 4px;">{{ $user->name }}</div>
                        <div>{{ $user->email }}</div>
                    </div>
                    <a href="{{ route('profile.show') }}" class="dropdown-item">Profile & Settings</a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item" style="width: 100%; background: none; border: none; text-align: left;">
                            Logout
                        </button>
                    </form> 
                </div>
            </div>
        </div>
    </div>

    <div class="sidebar">
        <div class="sidebar-item active" id="sidebarBoards" onclick="showBoards()">
            <div class="sidebar-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M4 4h7v11H4V4zm9 0h7v7h-7V4zm0 9h7v7h-7v-7z"/>
                </svg>
            </div>
            <span>Boards</span>
        </div>

        <!-- <div class="sidebar-section">
            <div class="sidebar-section-title"> User's Workspaces</div>
            @foreach($workspaces as $workspace)
            
                <a href="{{ route('workspaces.show', $workspace) }}" class="workspace-item">
                    <div class="workspace-icon {{ ['blue', 'green', 'purple', 'red', 'orange'][array_rand(['blue', 'green', 'purple', 'red', 'orange'])] }}">
                        {!! $workspace->display_icon !!}
                    </div>
                    <span>{{ $workspace->name }}</span>
                </a>
            @endforeach
        </div> -->

<!-- New User's Workspace  -->

@if(auth()->user()->isSystemAdmin())
<div class="sidebar-section">
    <div class="sidebar-section-title" onclick="toggleSection('my-ws-list','my-ws-arrow')" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
        Admin Workspaces
        <svg id="my-ws-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.3s; transform: rotate(-180deg);">
            <path d="m6 9 6 6 6-6"/>
        </svg>
    </div>
    <div id="my-ws-list" style="display: flex; flex-direction: column; gap: 4px; margin-top: 8px;">
        @forelse($ownedWorkspaces as $workspace)
            <a href="{{ route('workspaces.show', $workspace) }}" class="workspace-item">
                <div class="workspace-icon {{ $workspace->color }}">{!! $workspace->display_icon !!}</div>
                <span>{{ $workspace->name }}</span>
            </a>
        @empty
            <div style="font-size: 12px; color: #6b778c; padding: 4px;">No workspaces yet</div>
        @endforelse
    </div>        
</div>

@if($memberWorkspaces->count() > 0)
<!-- <div class="sidebar-section" style="margin-top: 4px;">
    <div class="sidebar-section-title" onclick="toggleSection('member-ws-list','member-ws-arrow')" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
        Member Workspaces
        <svg id="member-ws-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.3s; transform: rotate(-180deg);">
            <path d="m6 9 6 6 6-6"/>
        </svg>
    </div>
    <div id="member-ws-list" style="display: flex; flex-direction: column; gap: 4px; margin-top: 8px;">
        @foreach($memberWorkspaces as $workspace)
            <a href="{{ route('workspaces.show', $workspace) }}" class="workspace-item">
                <div class="workspace-icon {{ $workspace->color }}">{!! $workspace->display_icon !!}</div>
                <span>{{ $workspace->name }}</span>
            </a>
        @endforeach
    </div>
</div> -->

<div class="sidebar-section" style="margin-top: 4px;">
    <div class="sidebar-section-title" onclick="toggleSection('member-ws-list','member-ws-arrow')" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
        Member Workspaces
        <svg id="member-ws-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.3s; transform: rotate(0deg);">
            <path d="m6 9 6 6 6-6"/>
        </svg>
    </div>
    
    <div id="member-ws-list" style="display: none; flex-direction: column; gap: 4px; margin-top: 8px;">
        @foreach($memberWorkspaces as $workspace)
            <a href="{{ route('workspaces.show', $workspace) }}" class="workspace-item">
                <div class="workspace-icon {{ $workspace->color }}">{!! $workspace->display_icon !!}</div>
                <span>{{ $workspace->name }}</span>
            </a>
        @endforeach
    </div>
</div>
@endif
@else

{{-- MY WORKSPACES --}}
@if($ownedWorkspaces->count() > 0)
<div class="sidebar-section">
    <div class="sidebar-section-title" onclick="toggleSection('my-ws-list','my-ws-arrow')" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
        MY WORKSPACES
        <svg id="my-ws-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.3s; transform: rotate(-180deg);">
            <path d="m6 9 6 6 6-6"/>
        </svg>
    </div>
    <div id="my-ws-list" style="display: flex; flex-direction: column; gap: 4px; margin-top: 8px;">
        @foreach($ownedWorkspaces as $workspace)
            <a href="{{ route('workspaces.show', $workspace) }}" class="workspace-item">
                <div class="workspace-icon {{ $workspace->color }}">{!! $workspace->display_icon !!}</div>
                <span>{{ $workspace->name }}</span>
            </a>
        @endforeach
    </div>
</div>
@endif

{{-- MEMBER OF --}}
@if($memberWorkspaces->count() > 0)
<div class="sidebar-section" style="margin-top: 4px;">
    <div class="sidebar-section-title" onclick="toggleSection('joined-ws-list','joined-ws-arrow')" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
        GUEST WORKSPACES
        <svg id="joined-ws-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.3s; transform: rotate(-180deg);">
            <path d="m6 9 6 6 6-6"/>
        </svg>
    </div>
    <div id="joined-ws-list" style="display: flex; flex-direction: column; gap: 4px; margin-top: 8px;">
        @foreach($memberWorkspaces as $workspace)
            <a href="{{ route('workspaces.show', $workspace) }}" class="workspace-item">
                <div class="workspace-icon {{ $workspace->color }}">{!! $workspace->display_icon !!}</div>
                <span>{{ $workspace->name }}</span>
            </a>
        @endforeach
    </div>
</div>
@endif

@endif





    <!-- Create Workspace -->



    <div class="sidebar-section">
           
        </div> 

        <div class="sidebar-item" onclick="toggleArchivedBoards()" id="btnArchivedBoards">
            <div class="sidebar-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="color: #ff9f1a;">
                    <path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z"/>
                </svg>
            </div>
            <span>Archived Boards</span>
        </div>

        @if($user->isSystemAdmin())
            <div class="sidebar-section">
                <div class="sidebar-section-title">ADMIN TOOLS</div>
                <a href="{{ route('workspaces.create') }}" class="sidebar-item sidebar-item-admin" style="text-decoration: none;">
                    <div class="sidebar-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="color: #f79239;">
                            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                        </svg>
                    </div>
                    <span>Create Workspace</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="sidebar-item sidebar-item-admin" style="text-decoration: none;" id="btnUserLogin">
                    <div class="sidebar-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="color: #579dff;">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                    <span>User Login</span>
                </a>
                <a href="{{ route('admin.pending-approvals') }}" class="sidebar-item sidebar-item-admin" style="text-decoration: none;">
                    <div class="sidebar-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="color: #e2b200;">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                        </svg>
                    </div>
                    <span>Pending Approvals</span>
                    @php
                        $pendingCount = \App\Models\BoardJoinRequest::where('status','pending')->count();
                    @endphp
                    @if($pendingCount > 0)
                        <span style="margin-left:auto;background:#eb5a46;color:white;font-size:11px;font-weight:700;border-radius:10px;padding:1px 6px;">{{ $pendingCount }}</span>
                    @endif
                </a>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <div class="main-content">
       <!-- Session Messages -->
        <div style="padding: 16px 16px 0 16px;">
            @if(session('success'))
                <div style="background: #1c2b1d; border: 1px solid #2e7d32; color: #4caf50; padding: 12px; border-radius: 4px; margin-bottom: 16px; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background: #2b1c1c; border: 1px solid #c62828; color: #ef5350; padding: 12px; border-radius: 4px; margin-bottom: 16px; font-size: 14px;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Dashboard Content (Boards) -->
        <div id="boardsContent">
        <!-- Recently Viewed -->
        @if($recentBoards->count() > 0)
        <div class="section">
            <h2 class="section-header">Recently viewed</h2>
            <div class="boards-grid">
                @foreach($recentBoards as $board)
                    <a href="{{ route('boards.show', $board) }}" style="text-decoration: none;">
                        <div class="board-card">
                            @if($board->background_type == 'image' && $board->background_value)
                                <img src="{{ $board->background_value }}" alt="{{ $board->name }}" class="board-card-image">
                            @elseif($board->background_type == 'gradient')
                                <div class="board-card-gradient {{ $board->background_value ?: 'blue' }}"></div>
                            @else
                                <div class="board-card-gradient blue"></div>
                            @endif
                            <div class="board-card-title">{{ $board->name }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Filter Header (Hidden by default) -->
        <div id="clientFilterHeader" style="display: none; align-items: center; justify-content: space-between; margin-bottom: 24px; padding: 12px 16px; background: #1c2125; border: 1px solid #579dff; border-radius: 8px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 8px; height: 32px; background: #579dff; border-radius: 4px;"></div>
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: #579dff; text-transform: uppercase;">Filtered by Client</div>
                    <div id="filterClientName" style="font-size: 18px; font-weight: 600; color: #b6c2cf;">Client Name</div>
                </div>
            </div>
            <button onclick="clearClientFilter()" style="background: #2c333a; border: none; color: #b6c2cf; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600;">Clear Filter</button>
        </div>



        <!-- Your Workspaces -->
        @php
            $isAdmin = auth()->user()->isSystemAdmin();
            $displayOwnedWorkspaces = $isAdmin ? $workspaces : $ownedWorkspaces;
            $displayMemberWorkspaces = $isAdmin ? collect() : $memberWorkspaces;
        @endphp

        @if($displayOwnedWorkspaces->count() > 0)
        <div style="font-size: 11px; font-weight: 700; color: #9fadbc; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; padding: 0 4px;">
            {{ $isAdmin ? 'All Workspaces' : 'My Workspaces' }}
        </div>
        @endif

        @foreach($displayOwnedWorkspaces as $workspace)
            @if(isset($workspaceBoards[$workspace->id]) && $workspaceBoards[$workspace->id]->count() > 0)
            <div class="section">
                <div class="workspace-header">
                    <a href="{{ route('workspaces.show', $workspace) }}" style="text-decoration: none; color: inherit;">
                        <div class="workspace-header-title">
                            <div class="workspace-icon {{ $workspace->color }}">{!! $workspace->display_icon !!}</div>
                            <span>{{ $workspace->name }}</span>
                        </div>
                    </a>
                    @if(auth()->user()->isSystemAdmin())
                        <div class="workspace-actions">
                            <a href="{{ route('workspaces.edit', $workspace) }}" class="workspace-action-btn" title="Edit Workspace">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            </a>
                            <form id="deleteWorkspaceForm{{ $workspace->id }}" action="{{ route('workspaces.destroy', $workspace) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="workspace-action-btn delete" title="Delete Workspace" onclick="confirmDeleteWorkspace({{ $workspace->id }}, '{{ addslashes($workspace->name) }}')">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
                {{-- Workspace Clients Section --}}
                <div style="margin-top: 20px; margin-bottom: 12px; font-weight: 600; color: #9fadbc; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; padding-left: 4px; display: flex; align-items: center; gap: 8px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="opacity: 0.7;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    Clients ({{ $workspace->clients->count() }})
                </div>
                <div class="boards-grid" style="margin-bottom: 30px;">
                    @foreach($workspace->clients as $client)
                        @php
                            $firstBoard = $client->boards->first();
                            $clientUrl = $firstBoard ? route('boards.show', $firstBoard) : route('clients.show', $client);
                        @endphp
                        <a href="{{ $clientUrl }}" style="text-decoration: none;">
                            <div class="board-card client-card" data-client-id="{{ $client->id }}" style="position: relative; background: #22272b; border: 1px solid #38414a; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px; text-align: center; height: 170px; transition: transform 0.2s, border-color 0.2s;">
                                <div style="width: 70px; height: 70px; border-radius: 50%; overflow: hidden; border: 3px solid #38414a; margin-bottom: 12px; background: #1d2125; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    @if($client->image_path)
                                        <img src="{{ Storage::url($client->image_path) }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="board-card-gradient" style="display: none; align-items: center; justify-content: center; font-size: 28px; font-weight: bold; width: 100%; height: 100%; background: linear-gradient(135deg, #1c2b41 0%, #0052cc 100%); color: white;">
                                            {{ strtoupper(substr($client->name, 0, 1)) }}
                                        </div>
                                    @else
                                        <div class="board-card-gradient" style="display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold; width: 100%; height: 100%; background: linear-gradient(135deg, #1c2b41 0%, #0052cc 100%); color: white;">
                                            {{ strtoupper(substr($client->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div style="z-index: 1; width: 100%; overflow: hidden;">
                                    <div style="font-weight: 700; color: white; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $client->name }}">{{ $client->name }}</div>
                                    <div style="font-size: 11px; font-weight: 400; color: #9fadbc; margin-top: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $client->email }}">{{ $client->email }}</div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                    @if(Auth::user()->isSystemAdmin() || $workspace->isAdmin(Auth::id()))
                        <div class="board-card create" onclick="openClientModal({{ $workspace->id }})" style="cursor: pointer; background: #1d2125; border: 2px dashed #38414a; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 170px;">
                            <div style="font-size: 14px; font-weight: 600; color: #9fadbc;">Add Client</div>
                        </div>
                    @endif
                </div>

                {{-- Boards Section --}}
                <div style="margin-top: 20px; margin-bottom: 12px; font-weight: 600; color: #9fadbc; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; padding-left: 4px; display: flex; align-items: center; gap: 8px;">
                     <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="opacity: 0.7;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line></svg>
                     Boards
                </div>
                <div class="boards-grid">
                    @foreach($workspaceBoards[$workspace->id] as $board)
                        <div class="board-card-wrapper" data-board-id="{{ $board->id }}">
                            <a href="{{ route('boards.show', $board) }}" class="board-card-link">
                                <div class="board-card">
                                    @if($board->background_type == 'image' && $board->background_value)
                                        <img src="{{ $board->background_value }}" alt="{{ $board->name }}" class="board-card-image">
                                    @elseif($board->background_type == 'gradient')
                                        <div class="board-card-gradient {{ $board->background_value ?: 'blue' }}"></div>
                                    @else
                                        <div class="board-card-gradient blue"></div>
                                    @endif
                                    <div class="board-card-title">
                                        {{ $board->name }}
                                        @if($board->description)
                                            <br><span style="font-size: 12px; font-weight: 400;">{{ $board->description }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                            @if(Auth::user()->isSystemAdmin() || $workspace->isOwner(Auth::id()))
                            <button class="board-menu-btn" onclick="toggleBoardMenu({{ $board->id }}); return false;">
                                <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                                    <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                                </svg>
                            </button>
                            <div class="board-menu-dropdown" id="boardMenu{{ $board->id }}">
                                <button class="board-menu-item" onclick="editBoard({{ $board->id }}, '{{ addslashes($board->name) }}', '{{ addslashes($board->description ?? '') }}', '{{ $board->background_type }}', '{{ $board->background_value }}', {{ $board->workspace_id }});">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                    Edit
                                </button>
                                <button class="board-menu-item" onclick="archiveBoard({{ $board->id }}, '{{ addslashes($board->name) }}');">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z"/></svg>
                                    Archive
                                </button>
                                <button class="board-menu-item danger" onclick="deleteBoard({{ $board->id }}, '{{ addslashes($board->name) }}');">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                    Delete
                                </button>
                            </div>
                            @endif
                        </div>
                    @endforeach
                    @if($user->isSystemAdmin() || in_array($workspace->id, $canCreateBoardWorkspaceIds))
                    <div class="board-card create" onclick="openCreateBoardModal({{ $workspace->id }})" style="cursor: pointer;">
                        <div>Create new board</div>
                        @php $remainingBoards = 10 - $workspace->boards()->where('is_archived', false)->count(); @endphp
                        @if($remainingBoards > 0)
                            <div style="font-size: 12px; margin-top: 4px; color: #6b778c;">{{ $remainingBoards }} remaining</div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif
        @endforeach

        {{-- Member-only workspaces section (regular users only) --}}
        @if(!$isAdmin && $displayMemberWorkspaces->count() > 0)
        <div style="font-size: 11px; font-weight: 700; color: #9fadbc; text-transform: uppercase; letter-spacing: 1px; margin: 24px 0 12px; padding: 0 4px; border-top: 1px solid #38414a; padding-top: 20px;">
            Workspaces I'm a Member Of
        </div>
        @foreach($displayMemberWorkspaces as $workspace)
            @if(isset($workspaceBoards[$workspace->id]) && $workspaceBoards[$workspace->id]->count() > 0)
            <div class="section">
                <div class="workspace-header">
                    <a href="{{ route('workspaces.show', $workspace) }}" style="text-decoration: none; color: inherit;">
                        <div class="workspace-header-title">
                            <div class="workspace-icon {{ $workspace->color }}">{!! $workspace->display_icon !!}</div>
                            <span>{{ $workspace->name }}</span>
                        </div>
                    </a>
                </div>
                <div class="boards-grid">
                    @foreach($workspaceBoards[$workspace->id] as $board)
                        <div class="board-card-wrapper">
                            <a href="{{ route('boards.show', $board) }}" class="board-card-link">
                                <div class="board-card">
                                    @if($board->background_type == 'image' && $board->background_value)
                                        <img src="{{ $board->background_value }}" alt="{{ $board->name }}" class="board-card-image">
                                    @elseif($board->background_type == 'gradient')
                                        <div class="board-card-gradient {{ $board->background_value ?: 'blue' }}"></div>
                                    @else
                                        <div class="board-card-gradient blue"></div>
                                    @endif
                                    <div class="board-card-title">{{ $board->name }}</div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                {{-- Workspace Clients Section --}}
                <div style="margin-top: 20px; margin-bottom: 12px; font-weight: 600; color: #9fadbc; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; padding-left: 4px; display: flex; align-items: center; gap: 8px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="opacity: 0.7;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    Clients ({{ $workspace->clients->count() }})
                </div>
                <div class="boards-grid" style="margin-bottom: 30px;">
                    @foreach($workspace->clients as $client)
                        @php
                            $firstBoard = $client->boards->first();
                            $clientUrl = $firstBoard ? route('boards.show', $firstBoard) : route('clients.show', $client);
                        @endphp
                        <a href="{{ $clientUrl }}" style="text-decoration: none;">
                            <div class="board-card client-card" data-client-id="{{ $client->id }}" style="position: relative; background: #22272b; border: 1px solid #38414a; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px; text-align: center; height: 170px; transition: transform 0.2s, border-color 0.2s;">
                                <div style="width: 70px; height: 70px; border-radius: 50%; overflow: hidden; border: 3px solid #38414a; margin-bottom: 12px; background: #1d2125; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    @if($client->image_path)
                                        <img src="{{ Storage::url($client->image_path) }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="board-card-gradient" style="display: none; align-items: center; justify-content: center; font-size: 28px; font-weight: bold; width: 100%; height: 100%; background: linear-gradient(135deg, #1c2b41 0%, #0052cc 100%); color: white;">
                                            {{ strtoupper(substr($client->name, 0, 1)) }}
                                        </div>
                                    @else
                                        <div class="board-card-gradient" style="display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold; width: 100%; height: 100%; background: linear-gradient(135deg, #1c2b41 0%, #0052cc 100%); color: white;">
                                            {{ strtoupper(substr($client->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div style="z-index: 1; width: 100%; overflow: hidden;">
                                    <div style="font-weight: 700; color: white; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $client->name }}">{{ $client->name }}</div>
                                    <div style="font-size: 11px; font-weight: 400; color: #9fadbc; margin-top: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $client->email }}">{{ $client->email }}</div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                    @if(Auth::user()->isSystemAdmin() || $workspace->isAdmin(Auth::id()))
                        <div class="board-card create" onclick="openClientModal({{ $workspace->id }})" style="cursor: pointer; background: #1d2125; border: 2px dashed #38414a; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 170px;">
                            <div style="font-size: 14px; font-weight: 600; color: #9fadbc;">Add Client</div>
                        </div>
                    @endif
                </div>

                {{-- Boards Section --}}
                <div style="margin-top: 20px; margin-bottom: 12px; font-weight: 600; color: #9fadbc; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; padding-left: 4px; display: flex; align-items: center; gap: 8px;">
                     <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="opacity: 0.7;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line></svg>
                     Boards ({{ $workspaceBoards[$workspace->id]->count() }})
                </div>
            </div>
            @endif
        @endforeach
        @endif
        </div>
        <!-- End of boardsContent -->

        <div id="archivedBoardsContent" style="display: none; padding: 0 16px;">
            <div class="user-mgmt-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h2 class="section-header" style="margin-bottom: 0;">Archived Cards</h2>
            </div>
            <div id="archivedCardsContainer" style="display: grid; gap: 16px;">
                <div style="text-align: center; padding: 60px 20px; color: #9fadbc;">
                    <div style="font-size: 16px; margin-bottom: 8px;">Loading archived cards...</div>
                </div>
            </div>
        </div>
    </div>



    <div class="modal" id="editBoardModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Board</h3>
                <button class="modal-close" onclick="closeEditBoardModal()">&times;</button>
            </div>
            <form id="editBoardForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_name">Board Name</label>
                        <input type="text" id="edit_name" name="name" class="modal-input" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <input type="text" id="edit_description" name="description" class="modal-input">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeEditBoardModal()">Cancel</button>
                    <button type="submit" class="btn-create">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function toggleUserDropdown() {
            document.getElementById('userDropdown').classList.toggle('active');
        }

        function toggleArchivedBoards() {
            // console.log('toggleArchivedBoards called');
            const archived = document.getElementById('archivedBoardsContent');
            const boards = document.getElementById('boardsContent');
            const userMgmt = document.getElementById('userMgmtContent');
            const sidebar = document.getElementById('sidebarBoards');
            
            // console.log('Elements:', { archived, boards, userMgmt, sidebar });
            
            if (!archived) {
                console.error('archivedBoardsContent element not found!');
                return;
            }

            if (archived.style.display === 'block') {
                // console.log('Hiding archived boards');
                archived.style.display = 'none';
                if (boards) boards.style.display = 'block';
                localStorage.setItem('dashboard_view', 'boards');
                if (sidebar) sidebar.classList.add('active');
            } else {
                // console.log('Showing archived boards');
                archived.style.display = 'block';
                if (boards) boards.style.display = 'none';
                if (userMgmt) userMgmt.style.display = 'none';
                if (sidebar) sidebar.classList.remove('active');
                localStorage.setItem('dashboard_view', 'archived');
                
                // Load archived cards
                loadArchivedCards();
            }
        }

        async function loadArchivedCards() {
            const container = document.getElementById('archivedCardsContainer');
            container.innerHTML = '<div style="text-align: center; padding: 60px 20px; color: #9fadbc;"><div style="font-size: 16px; margin-bottom: 8px;">Loading archived cards...</div></div>';
            
            try {
                const response = await fetch('{{ route('archived-cards.all') }}', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success && data.cards.length > 0) {
                    let html = '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">';
                    data.cards.forEach(card => {
                        html += `
                            <div class="archived-card-dashboard" data-card-id="${card.id}" style="background: #282e33; border: 1px solid #3d444d; border-radius: 8px; padding: 0; overflow: hidden; transition: all 0.2s; cursor: pointer;" onmouseover="this.style.borderColor='#4d5761'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.3)'" onmouseout="this.style.borderColor='#3d444d'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                <!-- Card Header -->
                                <div style="padding: 16px; border-bottom: 1px solid #3d444d;">
                                    <div style="font-size: 15px; font-weight: 600; color: #e6edf3; margin-bottom: 12px; line-height: 1.4;">${escapeHtml(card.title)}</div>
                                    
                                    <!-- Location Info -->
                                    <div style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; color: #9fadbc;">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; opacity: 0.6;">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                <line x1="9" y1="3" x2="9" y2="21"></line>
                                            </svg>
                                            <span style="color: #6b778c;">in list</span>
                                            <strong style="color: #b6c2cf;">${escapeHtml(card.list_name)}</strong>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; opacity: 0.6;">
                                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                            </svg>
                                            <span style="color: #6b778c;">on board</span>
                                            <strong style="color: #b6c2cf;">${escapeHtml(card.board_name)}</strong>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Card Footer -->
                                <div style="padding: 12px 16px; background: #22272b; display: flex; justify-content: space-between; align-items: center;">
                                    <div style="font-size: 12px; color: #6b778c; display: flex; align-items: center; gap: 6px;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="opacity: 0.6;">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                        ${card.archived_at}
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div style="padding: 12px; background: #1d2125; display: flex; gap: 8px; border-top: 1px solid #3d444d;">
                                    <button onclick="event.stopPropagation(); restoreCardFromDashboard(${card.id}, ${card.board_id}, ${card.list_id})" style="flex: 1; padding: 8px 12px; background: #0c66e4; border: none; border-radius: 4px; color: white; font-size: 13px; font-weight: 500; cursor: pointer; transition: background 0.2s; display: flex; align-items: center; justify-content: center; gap: 6px;" onmouseover="this.style.background='#0052cc'" onmouseout="this.style.background='#0c66e4'">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="23 4 23 10 17 10"></polyline>
                                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                                        </svg>
                                        Send to board
                                    </button>
                                    <button onclick="event.stopPropagation(); deleteCardFromDashboard(${card.id}, ${card.board_id}, ${card.list_id})" style="padding: 8px 12px; background: #282e33; border: 1px solid #3d444d; border-radius: 4px; color: #e74c3c; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.background='#c9372c'; this.style.color='white'; this.style.borderColor='#c9372c'" onmouseout="this.style.background='#282e33'; this.style.color='#e74c3c'; this.style.borderColor='#3d444d'">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    container.innerHTML = html;
                } else {
                    container.innerHTML = `
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 100px 20px; color: #9fadbc; background: #282e33; border-radius: 12px; border: 2px dashed #3d444d;">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 20px; opacity: 0.3;">
                                <path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z"/>
                            </svg>
                            <h3 style="margin: 0; font-size: 20px; font-weight: 600; color: #b6c2cf;">No archived cards</h3>
                            <p style="margin-top: 12px; font-size: 14px; opacity: 0.7; max-width: 400px; text-align: center; line-height: 1.5;">Cards you archive from boards will appear here. You can restore them or delete them permanently.</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading archived cards:', error);
                container.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 100px 20px; color: #e74c3c; background: #282e33; border-radius: 12px; border: 2px solid #3d444d;">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-bottom: 16px; opacity: 0.5;">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <h3 style="margin: 0; font-size: 18px; font-weight: 600;">Error loading archived cards</h3>
                        <p style="margin-top: 8px; font-size: 14px; opacity: 0.7;">Please refresh the page and try again</p>
                    </div>
                `;
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        async function restoreCardFromDashboard(cardId, boardId, listId) {
            try {
                const response = await fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}/restore`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Remove card from DOM
                    document.querySelector(`[data-card-id="${cardId}"]`).remove();
                    
                    // Check if no cards left
                    const remaining = document.querySelectorAll('.archived-card-dashboard');
                    if (remaining.length === 0) {
                        loadArchivedCards();
                    }
                    
                    // Show success message
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Card restored to board',
                        showConfirmButton: false,
                        timer: 2000,
                        background: '#22272b',
                        color: '#b6c2cf'
                    });
                } else {
                    throw new Error(data.error || 'Failed to restore card');
                }
            } catch (error) {
                console.error('Error restoring card:', error);
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Failed to restore card',
                    showConfirmButton: false,
                    timer: 2000,
                    background: '#22272b',
                    color: '#b6c2cf'
                });
            }
        }

        async function deleteCardFromDashboard(cardId, boardId, listId) {
            const result = await Swal.fire({
                title: 'Delete card permanently?',
                text: 'This action cannot be undone',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c9372c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                background: '#22272b',
                color: '#b6c2cf'
            });
            
            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/boards/${boardId}/lists/${listId}/cards/${cardId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Remove card from DOM
                        document.querySelector(`[data-card-id="${cardId}"]`).remove();
                        
                        // Check if no cards left
                        const remaining = document.querySelectorAll('.archived-card-dashboard');
                        if (remaining.length === 0) {
                            loadArchivedCards();
                        }
                        
                        // Show success message
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Card deleted',
                            showConfirmButton: false,
                            timer: 2000,
                            background: '#22272b',
                            color: '#b6c2cf'
                        });
                    } else {
                        throw new Error(data.error || 'Failed to delete card');
                    }
                } catch (error) {
                    console.error('Error deleting card:', error);
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'Failed to delete card',
                        showConfirmButton: false,
                        timer: 2000,
                        background: '#22272b',
                        color: '#b6c2cf'
                    });
                }
            }
        }

        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('userDropdown');
            const avatar = document.querySelector('.user-avatar-header');
            if (!avatar.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });

        function showBoards() {
            const boards = document.getElementById('boardsContent');
            const userMgmt = document.getElementById('userMgmtContent');
            const archived = document.getElementById('archivedBoardsContent');
            const sidebar = document.getElementById('sidebarBoards');

            if (boards) boards.style.display = 'block';
            if (userMgmt) userMgmt.style.display = 'none';
            if (archived) archived.style.display = 'none';
            if (sidebar) sidebar.classList.add('active');
            localStorage.setItem('dashboard_view', 'boards');
        }

        function toggleUserManagement() {
            const userMgmt = document.getElementById('userMgmtContent');
            const boards = document.getElementById('boardsContent');
            const archived = document.getElementById('archivedBoardsContent');
            const sidebar = document.getElementById('sidebarBoards');
            
            if (!userMgmt) return;

            if (userMgmt.style.display === 'block') {
                userMgmt.style.display = 'none';
                if (boards) boards.style.display = 'block';
                localStorage.setItem('dashboard_view', 'boards');
                if (sidebar) sidebar.classList.add('active');
            } else {
                userMgmt.style.display = 'block';
                if (boards) boards.style.display = 'none';
                if (archived) archived.style.display = 'none';
                if (sidebar) sidebar.classList.remove('active');
                localStorage.setItem('dashboard_view', 'users');
            }
        }

        function openCreateBoardModal(workspaceId = null) {
            document.getElementById('createBoardModal').classList.add('active');
            if (workspaceId) {
                document.getElementById('workspace_id').value = workspaceId;
            }
        }

        function closeCreateBoardModal() {
            document.getElementById('createBoardModal').classList.remove('active');
        }

        function toggleBoardMenu(boardId) {
            const menu = document.getElementById('boardMenu' + boardId);
            document.querySelectorAll('.board-menu').forEach(m => {
                if (m !== menu) m.classList.remove('active');
            });
            menu.classList.toggle('active');
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.board-menu-btn') && !e.target.closest('.board-menu')) {
                document.querySelectorAll('.board-menu').forEach(m => m.classList.remove('active'));
            }
        });

        function editBoard(id, name, description, workspaceId) {
            document.getElementById('editBoardModal').classList.add('active');
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description;
            document.getElementById('editBoardForm').action = '/boards/' + id;
        }

        function closeEditBoardModal() {
            document.getElementById('editBoardModal').classList.remove('active');
        }

        function archiveBoard(id, name) {
            Swal.fire({
                title: 'Archive Board?',
                text: `Are you sure you want to archive "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0052cc',
                cancelButtonColor: '#6b778c',
                confirmButtonText: 'Archive',
                background: '#282e33',
                color: '#b6c2cf'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/boards/${id}/archive`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Archived!',
                                text: data.message,
                                icon: 'success',
                                background: '#282e33',
                                color: '#b6c2cf',
                                timer: 2000
                            });
                            setTimeout(() => location.reload(), 2000);
                        }
                    });
                }
            });
        }

        function deleteBoard(id, name) {
            Swal.fire({
                title: 'Delete Board?',
                text: `Are you sure you want to permanently delete "${name}"?`,
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#c9372c',
                cancelButtonColor: '#6b778c',
                confirmButtonText: 'Delete',
                background: '#282e33',
                color: '#b6c2cf'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/boards/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: data.message,
                                icon: 'success',
                                background: '#282e33',
                                color: '#b6c2cf',
                                timer: 2000
                            });
                            setTimeout(() => location.reload(), 2000);
                        }
                    });
                }
            });
        }

        function restoreBoard(id, name) {
            Swal.fire({
                title: 'Restore Board?',
                text: `Restore "${name}" to active boards?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0052cc',
                cancelButtonColor: '#6b778c',
                confirmButtonText: 'Restore',
                background: '#282e33',
                color: '#b6c2cf'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/boards/${id}/restore`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelector(`[data-board-id="${id}"]`).remove();
                            Swal.fire({
                                title: 'Restored!',
                                text: data.message,
                                icon: 'success',
                                background: '#282e33',
                                color: '#b6c2cf',
                                timer: 2000
                            });
                        }
                    });
                }
            });
        }

        function confirmDeleteWorkspace(id, name) {
            Swal.fire({
                title: 'Delete Workspace?',
                text: `Are you sure you want to delete "${name}"? All boards inside will be lost.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef5350',
                cancelButtonColor: '#6b778c',
                confirmButtonText: 'Yes, delete it',
                background: '#282e33',
                color: '#b6c2cf'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteWorkspaceForm' + id).submit();
                }
            });
        }

        function confirmDeleteUser(id, name) {
            Swal.fire({
                title: 'Delete User?',
                text: `Are you sure you want to erase ${name} forever? This cannot be undone.`,
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#ef5350',
                cancelButtonColor: '#6b778c',
                confirmButtonText: 'Yes, erase forever',
                background: '#282e33',
                color: '#b6c2cf'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteUserForm' + id).submit();
                }
            });
        }

        const savedView = localStorage.getItem('dashboard_view');
        if (savedView === 'users') {
            toggleUserManagement();
        } else if (savedView === 'archived') {
            toggleArchivedBoards();
        } else {
            showBoards();
        }

        // Background Picker Logic
        document.querySelectorAll('.bg-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remove active class from all
                document.querySelectorAll('.bg-option').forEach(opt => opt.classList.remove('active'));
                // Add to clicked
                this.classList.add('active');
                
                const type = this.dataset.type;
                const value = this.dataset.value;
                const preview = document.getElementById('boardPreview');
                
                document.getElementById('bg_type').value = type;
                document.getElementById('bg_value').value = value;
                
                if (type === 'image') {
                    preview.style.backgroundImage = `url('${value}')`;
                    preview.style.backgroundColor = 'transparent';
                } else {
                    preview.style.backgroundImage = 'none';
                    // Match the gradients for preview
                    const gradients = {
                        'blue': 'linear-gradient(135deg, #0079bf 0%, #5067c5 100%)',
                        'green': 'linear-gradient(135deg, #519839 0%, #4bbf6b 100%)',
                        'orange': 'linear-gradient(135deg, #d29034 0%, #f1bd6c 100%)',
                        'red': 'linear-gradient(135deg, #b04632 0%, #f26b52 100%)'
                    };
                    preview.style.background = gradients[value] || '#0c66e4';
                }
            });
        });

        // Initialize preview
        window.addEventListener('load', () => {
            const activeBg = document.querySelector('.bg-option.active');
            if (activeBg) activeBg.click();
        });
        function toggleWorkspaces() {
    const list = document.getElementById('workspaces-list');
    const arrow = document.getElementById('ws-arrow');
    if (list.style.display === "none") {
        list.style.display = "flex";
        arrow.style.transform = "rotate(180deg)";
    } else {
        list.style.display = "none";
        arrow.style.transform = "rotate(0deg)";
    }
}
function toggleSection(listId, arrowId) {
    const list = document.getElementById(listId);
    const arrow = document.getElementById(arrowId);
    
    if (list.style.display === "none" || list.style.display === "") {
        list.style.display = "flex";
        arrow.style.transform = "rotate(-180deg)"; // Open state
    } else {
        list.style.display = "none";
        arrow.style.transform = "rotate(0deg)"; // Closed state
    }
}
        // function toggleSection(listId, arrowId) {
        //     const list = document.getElementById(listId);
        //     const arrow = document.getElementById(arrowId);
        //     if (!list) return;
        //     if (list.style.display === 'none' || list.style.display === '') {
        //         list.style.display = 'flex';
        //         if (arrow) arrow.style.transform = 'rotate(-180deg)';
        //     } else {
        //         list.style.display = 'none';
        //         if (arrow) arrow.style.transform = 'rotate(0deg)';
        //     }
        // }
    </script>

    <script>
        // ===== BOARD & CARD SEARCH =====
        @php
            $dashBoardsData = collect($workspaceBoards)->flatten(1)->map(fn($b) => [
                'id'        => $b->id,
                'name'      => $b->name,
                'workspace' => $b->workspace->name ?? '',
                'bg_type'   => $b->background_type,
                'bg_value'  => $b->background_value,
            ])->values()->toArray();
        @endphp
        const dashAllBoards = @json($dashBoardsData);
        const dashAllCards = @json($allCards ?? []);

        function filterDashBoards() {
            const q = document.getElementById('dashBoardSearch').value.toLowerCase().trim();
            const dd = document.getElementById('dashSearchDropdown');
            const input = document.getElementById('dashBoardSearch');
            
            if (!q) { dd.style.display = 'none'; return; }

            // Search in boards
            const filteredBoards = dashAllBoards.filter(b => 
                b.name.toLowerCase().includes(q) || 
                b.workspace.toLowerCase().includes(q)
            );

            // Search in cards
            const filteredCards = dashAllCards.filter(c => 
                c.name.toLowerCase().includes(q) || 
                c.list_name.toLowerCase().includes(q) ||
                c.board_name.toLowerCase().includes(q)
            );

            const hasResults = filteredBoards.length > 0 || filteredCards.length > 0;

            if (hasResults) {
                let html = '';

                // Add board results
                if (filteredBoards.length > 0) {
                    html += '<div style="padding:8px 14px;color:#9fadbc;font-size:11px;font-weight:600;text-transform:uppercase;">Boards</div>';
                    html += filteredBoards.map(b => {
                        const bg = b.bg_type === 'image' ? `background-image:url('${b.bg_value}');background-size:cover;` : `background:${b.bg_value || '#0079bf'};`;
                        return `<a href="/boards/${b.id}" style="display:flex;align-items:center;gap:10px;padding:8px 14px;color:#b6c2cf;text-decoration:none;border-bottom:1px solid #2c333a;transition:background 0.15s;" onmouseover="this.style.background='#2c333a'" onmouseout="this.style.background='transparent'">
                            <div style="width:32px;height:24px;border-radius:3px;flex-shrink:0;${bg}"></div>
                            <div style="flex:1;"><div style="font-size:13px;font-weight:500;">${b.name}</div><div style="font-size:11px;color:#9fadbc;margin-top:2px;">${b.workspace}</div></div>
                        </a>`;
                    }).join('');
                }

                // Add card results
                if (filteredCards.length > 0) {
                    html += '<div style="padding:8px 14px;color:#9fadbc;font-size:11px;font-weight:600;text-transform:uppercase;margin-top:4px;">Cards</div>';
                    html += filteredCards.map(c => `
                        <a href="/boards/${c.board_id}/lists/${c.list_id}/cards/${c.id}" style="display:flex;align-items:center;gap:10px;padding:8px 14px;color:#b6c2cf;text-decoration:none;border-bottom:1px solid #2c333a;transition:background 0.15s;" onmouseover="this.style.background='#2c333a'" onmouseout="this.style.background='transparent'">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="flex-shrink:0;color:#9fadbc;"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 00.948-.684l1.498-4.493a1 1 0 011.502 0l1.498 4.493a1 1 0 00.948.684H19a2 2 0 012 2v2H3V5zm0 4h18v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg>
                            <div style="flex:1;"><div style="font-size:13px;font-weight:500;">${c.name}</div><div style="font-size:11px;color:#9fadbc;margin-top:2px;">${c.board_name} • ${c.list_name}</div></div>
                        </a>
                    `).join('');
                }

                dd.innerHTML = html;
            } else {
                dd.innerHTML = `<div style="padding:12px 14px;color:#9fadbc;font-size:13px;text-align:center;">No boards or cards found for "<strong style="color:#b6c2cf;">${q}</strong>"</div>`;
            }
            
            // Position dropdown below search input
            const inputRect = input.getBoundingClientRect();
            dd.style.position = 'fixed';
            dd.style.top = (inputRect.bottom + 5) + 'px';
            dd.style.left = inputRect.left + 'px';
            dd.style.width = inputRect.width + 'px';
            dd.style.display = 'block';
        }

        document.addEventListener('click', e => {
            if (!e.target.closest('.search-bar')) {
                const dd = document.getElementById('dashSearchDropdown');
                if (dd) dd.style.display = 'none';
            }
        });

        // ===== NOTIFICATION SYSTEM =====
        let notifOpen = false;
        let lastNotifIds = new Set();
const BASE_URL = '{{ rtrim(url('/'), '/') }}';
//   let lastNotifIds = new Set();

async function loadNotifications() {
    try {
        const res = await fetch(`${BASE_URL}/notifications`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        if (!res.ok) return;

        const data = await res.json();

        // Browser notification
        if (typeof showBrowserNotif === 'function' && lastNotifIds.size > 0) {
            (data.notifications || []).forEach(n => {
                if (!n.read && !lastNotifIds.has(n.id)) {
                    showBrowserNotif(
                        'Trello',
                        n.message,
                        n.card_id && n.list_id
                            ? `${BASE_URL}/boards/${n.board_id}/lists/${n.list_id}/cards/${n.card_id}`
                            : `${BASE_URL}/boards/${n.board_id}`
                    );
                }
            });
        }

        lastNotifIds = new Set((data.notifications || []).map(n => n.id));

        // Badge update
        const badge = document.getElementById('notifBadge');
        if (badge) {
            badge.style.display = data.unread > 0 ? 'flex' : 'none';
            if (data.unread > 0) {
                badge.textContent = data.unread > 9 ? '9+' : data.unread;
            }
        }

        // Notification list
        const list = document.getElementById('notifList');
        if (!list) return;

        if (!data.notifications || !data.notifications.length) {
            list.innerHTML = `<div style="padding:24px;text-align:center;color:#9fadbc;font-size:13px;">No notifications</div>`;
            return;
        }

        list.innerHTML = data.notifications.map(n => `
            <div onclick="goToNotif('${n.board_id}','${n.list_id}','${n.card_id}','${n.id}')"
                 style="padding:12px 16px;border-bottom:1px solid #2c333a;cursor:pointer;background:${n.read ? 'transparent' : '#1d2d3e'};"
                 onmouseover="this.style.background='#2c333a'"
                 onmouseout="this.style.background='${n.read ? 'transparent' : '#1d2d3e'}'">

                <div style="font-size:13px;color:#b6c2cf;line-height:1.4;">
                    ${n.message}
                </div>

                <div style="font-size:11px;color:#9fadbc;margin-top:4px;">
                    ${n.board_name} • ${n.diff}
                </div>
            </div>
        `).join('');

    } catch (e) {
        console.error('Notification error:', e);
    }
}

window.toggleNotifDropdown = function () {
    const dd = document.getElementById('notifDropdown');
    notifOpen = !notifOpen;
    dd.style.display = notifOpen ? 'flex' : 'none';

    if (notifOpen) {
        loadNotifications();
        setTimeout(() => document.addEventListener('click', closeNotifOutside), 0);
    }
};
        function closeNotifOutside(e) {
            const wrap = document.getElementById('notifBellWrap');
            if (wrap && !wrap.contains(e.target)) { document.getElementById('notifDropdown').style.display = 'none'; notifOpen = false; document.removeEventListener('click', closeNotifOutside); }
        }

        function markAllRead() {
            fetch(`${BASE_URL}/notifications/mark-read`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }).then(() => loadNotifications());
        }

        function goToNotif(boardId, listId, cardId, notifId) {
            // Mark notification as read
            fetch(`${BASE_URL}/notifications/mark-read`, { 
                method: 'POST', 
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                }, 
                body: JSON.stringify({ id: notifId }) 
            }).catch(() => {});
            
            // Navigate to appropriate page
            if (cardId && cardId !== 'null' && cardId !== 'undefined' && boardId && boardId !== 'null') {
                fetch(`${BASE_URL}/boards/${boardId}/cards/${cardId}`, { method: 'HEAD' })
                    .then(r => { 
                        if (r.ok) {
                            window.location.href = `${BASE_URL}/boards/${boardId}/cards/${cardId}`;
                        } else {
                            Swal.fire({
                                title: 'Card Not Found',
                                text: 'This card has been deleted. Redirecting to board...',
                                icon: 'info',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = `${BASE_URL}/boards/${boardId}`;
                            });
                        }
                    })
                    .catch(() => { 
                        if (listId && listId !== 'null' && listId !== 'undefined') {
                            window.location.href = `${BASE_URL}/boards/${boardId}/lists/${listId}/cards/${cardId}`;
                        } else {
                            window.location.href = `${BASE_URL}/boards/${boardId}`;
                        }
                    });
            } else if (boardId && boardId !== 'null') {
                window.location.href = `${BASE_URL}/boards/${boardId}`;
            }
        }

        loadNotifications();
        setInterval(loadNotifications, 2000);

        // Real-time board access check — hide boards user no longer has access to
        async function checkBoardAccess() {
            try {
                const res = await fetch(`${BASE_URL}/user/accessible-boards`, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
                if (!res.ok) return;
                const data = await res.json();
                if (!data.board_ids) return;
                const accessibleIds = new Set(data.board_ids.map(String));
                document.querySelectorAll('.board-card-wrapper[data-board-id]').forEach(el => {
                    const bid = el.getAttribute('data-board-id');
                    if (!accessibleIds.has(bid)) {
                        el.style.transition = 'opacity 0.3s';
                        el.style.opacity = '0';
                        setTimeout(() => el.remove(), 300);
                    }
                });
            } catch(e) {}
        }

        setInterval(checkBoardAccess, 5000);
    </script>

    <!-- Create Board Modal -->
    <div class="modal" id="createBoardModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Create Board</h3>
                <button class="modal-close" onclick="closeCreateBoardModal()">&times;</button>
            </div>
            <form action="{{ route('boards.store') }}" method="POST" id="createBoardForm">
                @csrf
                <div class="modal-body">
                    <div class="board-preview-card" id="boardPreview" style="background-color: #0c66e4;">
                        <span class="board-preview-title" id="previewTitle">Board title</span>
                    </div>
                    <div class="form-group">
                        <label class="bg-picker-label">Background</label>
                        <div class="bg-options-grid">
                            <div class="bg-option active" data-type="image" data-value="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=400&q=80" style="background-image: url('https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=400&q=80')"></div>
                            <div class="bg-option" data-type="image" data-value="https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=400&q=80" style="background-image: url('https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=400&q=80')"></div>
                            <div class="bg-option" data-type="image" data-value="https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=400&q=80" style="background-image: url('https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=400&q=80')"></div>
                            <div class="bg-option" data-type="image" data-value="https://images.unsplash.com/photo-1472214103451-9374bd1c798e?auto=format&fit=crop&w=400&q=80" style="background-image: url('https://images.unsplash.com/photo-1472214103451-9374bd1c798e?auto=format&fit=crop&w=400&q=80')"></div>
                            <div class="bg-option" data-type="gradient" data-value="blue" style="background: linear-gradient(135deg, #0079bf 0%, #5067c5 100%);"></div>
                            <div class="bg-option" data-type="gradient" data-value="green" style="background: linear-gradient(135deg, #519839 0%, #4bbf6b 100%);"></div>
                            <div class="bg-option" data-type="gradient" data-value="orange" style="background: linear-gradient(135deg, #d29034 0%, #f1bd6c 100%);"></div>
                            <div class="bg-option" data-type="gradient" data-value="red" style="background: linear-gradient(135deg, #b04632 0%, #f26b52 100%);"></div>
                        </div>
                        <input type="hidden" name="background_type" id="bg_type" value="image">
                        <input type="hidden" name="background_value" id="bg_value" value="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=400&q=80">
                    </div>
                    <div class="form-group">
                        <label for="name" class="bg-picker-label">Board Title *</label>
                        <input type="text" id="name" name="name" class="modal-input" placeholder="Enter board title" required oninput="document.getElementById('previewTitle').innerText = this.value || 'Board title'">
                    </div>
                    <div class="form-group">
                        <label for="workspace_id" class="bg-picker-label">Workspace</label>
                        <select id="workspace_id" name="workspace_id" class="modal-input" required>
                            @foreach($canCreateBoardWorkspaces as $workspace)
                                <option value="{{ $workspace->id }}">{{ $workspace->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeCreateBoardModal()">Cancel</button>
                    <button type="submit" class="btn-create" style="background: #0c66e4;">Create Board</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Add Client Modal -->
    <div class="modal" id="addClientModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add Client</h3>
                <button class="modal-close" onclick="closeClientModal()">&times;</button>
            </div>
            <form action="{{ route('clients.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="workspace_id" id="client_workspace_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="bg-picker-label">Client Image</label>
                        <input type="file" name="image" class="modal-input" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label class="bg-picker-label">Client Name *</label>
                        <input type="text" name="name" class="modal-input" placeholder="Enter client name" required>
                    </div>
                    <div class="form-group">
                        <label class="bg-picker-label">Email *</label>
                        <input type="email" name="email" class="modal-input" placeholder="Enter client email" required>
                    </div>
                    <div class="form-group">
                        <label class="bg-picker-label">Father's Name</label>
                        <input type="text" name="father_name" class="modal-input" placeholder="Enter father's name">
                    </div>
                    <div class="form-group">
                        <label class="bg-picker-label">Phone Number</label>
                        <input type="text" name="phone" class="modal-input" placeholder="Enter phone number">
                    </div>
                    <div class="form-group" id="workspace_select_group">
                        <label for="modal_workspace_id" class="bg-picker-label">Workspace *</label>
                        <select id="modal_workspace_id" class="modal-input">
                            <option value="">Select Workspace</option>
                            @foreach($canCreateBoardWorkspaces as $ws)
                                <option value="{{ $ws->id }}">{{ $ws->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeClientModal()">Cancel</button>
                    <button type="submit" class="btn-create" style="background: #0052cc;">Add Client</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openClientModal(workspaceId = null) {
            const wsGroup = document.getElementById('workspace_select_group');
            const wsSelect = document.getElementById('modal_workspace_id');
            const hiddenWs = document.getElementById('client_workspace_id');
            
            if (workspaceId) {
                hiddenWs.value = workspaceId;
                if (wsGroup) wsGroup.style.display = 'none';
                if (wsSelect) wsSelect.removeAttribute('required');
            } else {
                hiddenWs.value = '';
                if (wsGroup) wsGroup.style.display = 'block';
                if (wsSelect) wsSelect.setAttribute('required', 'required');
                
                // Add event listener to update hidden field when dropdown changes
                if (wsSelect) {
                    wsSelect.onchange = function() {
                        hiddenWs.value = this.value;
                    };
                }
            }
            document.getElementById('addClientModal').style.display = 'flex';
        }
        function closeClientModal() {
            document.getElementById('addClientModal').style.display = 'none';
        }

        function filterByClient(clientId, clientName, boardIds) {
            // Show filter header
            document.getElementById('clientFilterHeader').style.display = 'flex';
            document.getElementById('filterClientName').innerText = clientName;
            
            // Hide recently viewed and clients section while filtering
            document.querySelector('.client-section').style.display = 'none';
            const recentViewed = document.querySelector('.section-header')?.parentElement;
            if (recentViewed && recentViewed.textContent.includes('Recently viewed')) {
                recentViewed.style.display = 'none';
            }

            // Filter boards
            const boardCards = document.querySelectorAll('.boards-grid a');
            boardCards.forEach(anchor => {
                const boardLink = anchor.getAttribute('href');
                const boardId = boardLink.split('/').pop();
                
                if (boardIds.includes(parseInt(boardId))) {
                    anchor.parentElement.style.display = 'block';
                } else {
                    anchor.parentElement.style.display = 'none';
                }
            });

            // Hide empty workspaces
            const workspaces = document.querySelectorAll('.section:not(.client-section)');
            workspaces.forEach(ws => {
                const visibleBoards = ws.querySelectorAll('.boards-grid > *:not([style*="display: none"])');
                if (visibleBoards.length === 0) {
                    ws.style.display = 'none';
                } else {
                    ws.style.display = 'block';
                }
            });
        }

        function clearClientFilter() {
            document.getElementById('clientFilterHeader').style.display = 'none';
            document.querySelector('.client-section').style.display = 'block';
            
            // Restore visibility
            const allSections = document.querySelectorAll('.section');
            allSections.forEach(section => section.style.display = 'block');
            
            const boardCards = document.querySelectorAll('.boards-grid > *');
            boardCards.forEach(card => card.style.display = 'block');
        }
    </script>
</body>