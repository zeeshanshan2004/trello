<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <title>Boards - {{ config('app.name', 'Trello') }}</title>

    <!-- Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #1d2125;
            color: #b6c2cf;
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
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
            font-family: inherit;
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
            overflow-wrap: break-word;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 40px;
            bottom: 0;
            width: 282px;
            background: #22272b;
            border-right: 1px solid #38414a;
            overflow-y: auto;
            overflow-x: hidden; /* Prevent horizontal scroll */
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
            transition: background 0.2s;
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
            display: flex;
            align-items: center;
            justify-content: center;
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
            letter-spacing: 0.5px;
        }

        /* Workspace Item Styles */
        .workspace-item {
            position: relative;
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
            transition: all 0.15s ease;
            overflow: hidden;
        }

        .workspace-item:hover {
            background: #2c333a;
            color: #ffffff;
        }

        .workspace-item span {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
        }

        .workspace-actions {
            display: none;
            align-items: center;
            gap: 4px;
            margin-left: auto;
            flex-shrink: 0;
        }

        .workspace-item:hover .workspace-actions {
            display: flex;
        }

        .workspace-action-btn {
            width: 28px;
            height: 28px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9fadbc;
            cursor: pointer;
            border: none;
            background: transparent;
            transition: all 0.15s ease;
            padding: 0;
        }

        .workspace-action-btn:hover {
            background: #3c444d;
            color: #ffffff;
        }

        .workspace-action-btn.delete:hover {
            background: #c9372c;
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
            flex-shrink: 0;
            text-transform: uppercase;
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

        .workspace-subitems {
            padding-left: 40px;
            margin-top: 4px;
        }

        .workspace-subitem {
            padding: 6px 12px;
            font-size: 13px;
            color: #9fadbc;
            cursor: pointer;
            border-radius: 4px;
        }

        .workspace-subitem:hover {
            background: #2c333a;
        }

        .upgrade-section {
            margin-top: 24px;
            padding: 16px;
            background: #2c333a;
            border-radius: 8px;
            margin-bottom: 16px;
        }

        .upgrade-title {
            font-size: 14px;
            font-weight: 600;
            color: white;
            margin-bottom: 8px;
        }

        .upgrade-desc {
            font-size: 12px;
            color: #b6c2cf;
            line-height: 1.5;
            margin-bottom: 12px;
        }

        .btn-upgrade {
            width: 100%;
            padding: 8px;
            background: #7c3aed;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }

        .btn-upgrade:hover {
            background: #8b5cf6;
        }

        /* Main Content */
        /* Main Content */
        .main-content {
            margin-left: 282px;
            margin-top: 40px;
            padding: 24px 24px;
            min-height: 100vh;
            background: #1d2125;
            width: calc(100vw - 282px);
            overflow-x: hidden;
            overflow-y: auto;
        }

        .section {
            margin-bottom: 32px;
        }

        /* User Management Styles - Complete Rewrite */
        .user-mgmt-container {
            display: none;
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0;
        }
        
        .user-mgmt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 2px solid #38414a;
        }
        
        .user-mgmt-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
        }

        .user-table-card {
            background: #22272b;
            border: 1px solid #38414a;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
            color: #b6c2cf;
        }
        
        .user-table thead {
            background: #2c333a;
            border-bottom: 2px solid #38414a;
        }
        
        .user-table thead th {
            padding: 16px 20px;
            text-align: left;
            font-size: 13px;
            font-weight: 700;
            color: #9fadbc;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .user-table tbody tr {
            border-bottom: 1px solid #2c333a;
            transition: background 0.2s;
        }
        
        .user-table tbody tr:hover {
            background: #2c333a;
        }
        
        .user-table tbody tr:last-child {
            border-bottom: none;
        }
        
        .user-table tbody td {
            padding: 18px 20px;
            font-size: 14px;
            color: #b6c2cf;
        }
        
        .user-table tbody td:first-child {
            font-weight: 600;
            color: #ffffff;
        }
        
        .user-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .user-status-badge.active {
            background: rgba(87, 242, 135, 0.15);
            color: #57f287;
        }
        
        .user-status-badge.inactive {
            background: rgba(237, 66, 69, 0.15);
            color: #ed4245;
        }
        
        .user-status-badge::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
        }
        
        .user-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        
        .user-action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }
        
        .user-action-btn.revoke {
            background: #ed4245;
            color: white;
        }
        
        .user-action-btn.revoke:hover {
            background: #c03537;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(237, 66, 69, 0.3);
        }
        
        .user-action-btn.delete {
            background: transparent;
            color: #ed4245;
            border: 1px solid #ed4245;
        }
        
        .user-action-btn.delete:hover {
            background: rgba(237, 66, 69, 0.1);
        }
        
        .user-action-btn.current {
            background: #5865f2;
            color: white;
            cursor: default;
        }

        .user-table th {
            text-align: left;
            padding: 12px 16px;
            background: #2c333a;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #8c9cb8;
            border-bottom: 1px solid #38414a;
        }

        .user-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #38414a;
            font-size: 14px;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        .user-table tr:hover {
            background: #282e33;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active { background: rgba(34, 197, 94, 0.2); color: #4ade80; }
        .status-inactive { background: rgba(239, 68, 68, 0.2); color: #f87171; }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            font-family: inherit;
            transition: opacity 0.2s;
        }

        .btn-grant { background: #0052cc; color: white; }
        .btn-revoke { background: #ae2e24; color: white; }
        .btn-delete { background: #323940; color: #b6c2cf; margin-left: 8px; }
        .btn-action:hover { opacity: 0.9; }

        .user-table-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        /* Add User Modal Styles */
        .admin-modal {
            max-width: 450px !important;
        }
        
        .form-row {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
        }
        
        .form-row > div {
            flex: 1;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            cursor: pointer;
        }

        .checkbox-group input {
            cursor: pointer;
        }

        .btn-cancel {
            background: transparent;
            color: #b6c2cf;
            border: 1px solid #38414a;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }

        .btn-cancel:hover {
            background: #2c333a;
            border-color: #45505c;
            color: white;
        }

        /* Archived Boards Layout */
        .archived-boards-content {
            display: none; /* Toggled via JS */
            max-width: 100%;
            width: 100%;
            margin: 0;
            padding-top: 16px;
        }

        .modal-input {
            width: 100%;
            padding: 10px 12px;
            background: #22272b !important;
            border: 1px solid #38414a !important;
            border-radius: 4px;
            color: #b6c2cf !important;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s;
        }

        .modal-input:focus {
            outline: none;
            border-color: #0052cc !important;
            background: #282e33 !important;
        }

        .section-header {
            font-size: 16px;
            font-weight: 600;
            color: #b6c2cf;
            margin-bottom: 16px;
        }

        .boards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }

        .board-card-wrapper {
            position: relative;
        }
        
        .board-card-link {
            text-decoration: none;
            display: block;
        }

        .board-card {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s;
            height: 96px;
        }

        .board-card:hover {
            transform: translateY(-2px);
        }
        
        .board-card-wrapper:hover .board-menu-btn {
            display: flex !important;
        }

        .board-card-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .board-card-gradient {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .board-card-gradient.blue {
            background: linear-gradient(135deg, #0052cc 0%, #0065ff 100%);
        }

        .board-card-gradient.purple {
            background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%);
        }

        .board-card-gradient.orange {
            background: linear-gradient(135deg, #f97316 0%, #fb923c 100%);
        }

        .board-card-title {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 12px;
            background: rgba(0, 0, 0, 0.4);
            color: white;
            font-size: 14px;
            font-weight: 600;
        }
        
        .board-menu-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 32px;
            height: 32px;
            border-radius: 4px;
            background: rgba(0, 0, 0, 0.5);
            border: none;
            color: white;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            z-index: 100;
        }
        
        .board-menu-btn:hover {
            background: rgba(0, 0, 0, 0.8);
            transform: scale(1.1);
        }
        
        .board-menu-btn svg {
            width: 18px;
            height: 18px;
        }
        
        .board-menu-dropdown {
            position: absolute;
            top: 44px;
            right: 8px;
            background: #282e33;
            border: 1px solid #38414a;
            border-radius: 8px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.6);
            min-width: 180px;
            z-index: 1001;
            display: none;
            overflow: hidden;
        }
        
        .board-menu-dropdown.active {
            display: block;
        }
        
        .board-menu-item {
            padding: 12px 16px;
            color: #b6c2cf;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            font-family: inherit;
            transition: background 0.2s;
        }
        
        .board-menu-item:hover {
            background: #323940;
        }
        
        .board-menu-item:first-child {
            border-radius: 8px 8px 0 0;
        }
        
        .board-menu-item:last-child {
            border-radius: 0 0 8px 8px;
        }
        
        .board-menu-item.danger {
            color: #e74c3c;
        }
        
        .board-menu-item.danger:hover {
            background: rgba(231, 76, 60, 0.1);
        }
        
        .board-menu-item svg {
            width: 16px;
            height: 16px;
        }

        .board-card.create {
            background: #22272b;
            border: 2px dashed #38414a;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #b6c2cf;
            font-size: 14px;
            font-weight: 500;
        }

        .board-card.create:hover {
            background: #2c333a;
            border-color: #45505c;
        }

        /* Archived Boards Styles - Complete Rewrite */
        #archivedBoardsContent {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0;
        }
        
        #archivedBoardsContent .section-header {
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 2px solid #38414a;
        }
        
        .archived-board-wrapper {
            position: relative;
            transition: transform 0.3s, opacity 0.3s;
        }
        
        .archived-board-card {
            opacity: 0.8;
            cursor: default;
            pointer-events: none;
            position: relative;
            filter: grayscale(30%) brightness(0.85);
            transition: all 0.3s;
            border: 2px solid #38414a;
        }
        
        .archived-board-wrapper:hover .archived-board-card {
            opacity: 0.95;
            filter: grayscale(10%) brightness(0.95);
            border-color: #4a5568;
        }
        
        .archived-board-card::before {
            content: 'ARCHIVED';
            position: absolute;
            top: 8px;
            left: 8px;
            background: rgba(237, 66, 69, 0.9);
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.5px;
            z-index: 5;
        }
        
        .archived-board-actions {
            position: absolute;
            bottom: 12px;
            left: 12px;
            right: 12px;
            display: flex;
            gap: 10px;
            z-index: 10;
            pointer-events: auto;
        }
        
        .archived-btn {
            flex: 1;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            font-family: inherit;
            box-shadow: 0 4px 8px rgba(0,0,0,0.4);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .restore-btn {
            background: linear-gradient(135deg, #0079bf 0%, #026aa7 100%);
            color: white;
        }
        
        .restore-btn:hover {
            background: linear-gradient(135deg, #026aa7 0%, #01547a 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 121, 191, 0.4);
        }
        
        .restore-btn:active {
            transform: translateY(0);
        }
        
        .delete-btn {
            background: linear-gradient(135deg, #ed4245 0%, #c03537 100%);
            color: white;
        }
        
        .delete-btn:hover {
            background: linear-gradient(135deg, #c03537 0%, #9e2b2d 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(237, 66, 69, 0.4);
        }
        
        .delete-btn:active {
            transform: translateY(0);
        }
        
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 120px 40px;
            color: #9fadbc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 600px;
            background: radial-gradient(circle at center, rgba(255,255,255,0.02) 0%, transparent 70%);
        }
        
        .empty-state svg {
            opacity: 0.2;
            margin-bottom: 32px;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .empty-state-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #ffffff;
        }
        
        .empty-state-text {
            font-size: 16px;
            opacity: 0.7;
            color: #9fadbc;
            max-width: 400px;
        }

        .workspace-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .workspace-header-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
            font-weight: 600;
            color: #b6c2cf;
        }

        .workspace-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }

        .workspace-nav-btn {
            padding: 6px 12px;
            background: transparent;
            border: 1px solid #38414a;
            border-radius: 4px;
            color: #b6c2cf;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            font-family: inherit;
        }

        .workspace-nav-btn:hover {
            background: #2c333a;
            border-color: #45505c;
        }

        .workspace-nav-btn.upgrade {
            background: #7c3aed;
            border-color: #7c3aed;
            color: white;
        }

        .workspace-nav-btn.upgrade:hover {
            background: #8b5cf6;
        }

        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
            }

            .main-content {
                margin-left: 240px;
                width: calc(100vw - 240px);
                padding: 20px 20px;
            }
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }

        .modal-overlay.active {
            display: flex;
        }

        .create-board-modal {
            background: #22272b;
            border-radius: 8px;
            width: 100%;
            max-width: 420px;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #38414a;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: white;
        }

        .close-modal-btn {
            background: none;
            border: none;
            color: #b6c2cf;
            font-size: 24px;
            cursor: pointer;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
        }

        .close-modal-btn:hover {
            background: #2c333a;
        }

        .modal-body {
            padding: 20px;
        }

        .board-preview-modal {
            width: 100%;
            height: 150px;
            border-radius: 6px;
            margin-bottom: 16px;
            position: relative;
            overflow: hidden;
            background: #0052cc;
        }

        .board-preview-modal img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .board-preview-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 16px;
            background: rgba(0, 0, 0, 0.3);
            color: white;
            font-weight: 600;
        }

        .background-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 6px;
            margin-bottom: 8px;
        }

        .background-option {
            width: 100%;
            aspect-ratio: 1;
            border-radius: 4px;
            cursor: pointer;
            border: 2px solid transparent;
            overflow: hidden;
            position: relative;
        }

        .background-option:hover {
            border-color: #0052cc;
        }

        .background-option.selected {
            border-color: #0052cc;
            border-width: 3px;
        }

        .background-option img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .background-gradient {
            width: 100%;
            height: 100%;
        }

        .bg-blue { background: linear-gradient(135deg, #0052cc 0%, #0065ff 100%); }
        .bg-light-blue { background: linear-gradient(135deg, #0c66e4 0%, #1c88ff 100%); }
        .bg-medium-blue { background: linear-gradient(135deg, #1c88ff 0%, #4db8ff 100%); }
        .bg-purple-pink { background: linear-gradient(135deg, #7c3aed 0%, #ec4899 100%); }
        .bg-pink-purple { background: linear-gradient(135deg, #ec4899 0%, #7c3aed 100%); }

        .modal-form-group {
            margin-bottom: 16px;
        }

        .modal-form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #b6c2cf;
            margin-bottom: 8px;
        }

        .modal-form-group .required {
            color: #c9372c;
        }

        .modal-form-group input[type="text"],
        .modal-form-group input[type="email"],
        .modal-form-group input[type="password"],
        .modal-form-group select {
            width: 100%;
            padding: 10px 12px;
            background: #1d2125;
            border: 2px solid #38414a;
            border-radius: 4px;
            color: #b6c2cf;
            font-size: 14px;
            font-family: inherit;
        }

        .modal-form-group input[type="text"]:focus,
        .modal-form-group input[type="email"]:focus,
        .modal-form-group input[type="password"]:focus,
        .modal-form-group select:focus {
            outline: none;
            border-color: #0052cc;
        }

        .modal-form-group input.error {
            border-color: #c9372c;
        }

        .error-message-modal {
            color: #c9372c;
            font-size: 12px;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .workspace-info-modal {
            font-size: 12px;
            color: #9fadbc;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #38414a;
            line-height: 1.5;
        }

        .btn-submit-modal {
            width: 100%;
            padding: 10px;
            background: #0052cc;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            margin-top: 16px;
        }

        .btn-submit-modal:hover {
            background: #0065ff;
        }

        .btn-submit-modal:disabled {
            background: #38414a;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 12px 8px;
            }

            .search-bar {
                display: none;
            }

            .background-grid {
                grid-template-columns: repeat(4, 1fr);
            }
            
            .create-board-modal {
                max-width: 90%;
                margin: 20px;
            }
      
        }
    </style>
</head>
<body>
    <!-- Top Header -->
    <div class="top-header">
        <div class="header-left">
            <svg class="grid-icon" viewBox="0 0 16 16" fill="currentColor">
                <path d="M2 2h4v4H2V2zm6 0h4v4H8V2zm6 0h4v4h-4V2zM2 8h4v4H2V8zm6 0h4v4H8V8zm6 0h4v4h-4V8zM2 14h4v4H2v-4zm6 0h4v4H8v-4zm6 0h4v4h-4v-4z"/>
            </svg>
            <div class="trello-logo-header">
                <div class="trello-logo-small" style = "color "></div>
                <span class="trello-text-header">Trello</span>
            </div>
        </div>
        <div class="search-bar">
            <svg class="search-icon" viewBox="0 0 16 16" fill="currentColor">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
            </svg>
            <input type="text" class="search-input" placeholder="Search">
        </div>
        <div class="header-right">
            @if($user->isSystemAdmin() || count($canCreateBoardWorkspaceIds) > 0)
                <button type="button" class="btn-create" onclick="openCreateBoardModal()" style="margin-right: 8px;">Create</button>
            @endif
            <div class="header-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
            </div>
            <div class="header-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                </svg>
            </div>
            <div class="user-avatar-header" id="userAvatar" onclick="toggleUserDropdown()">
                {{ substr(auth()->user()->name, 0, 2) }}
                <div class="user-dropdown" id="userDropdown">
                    <div class="dropdown-user-info">
                        <strong>{{ auth()->user()->name }}</strong><br>
                        {{ auth()->user()->email }}
                    </div>
                    <a href="#" class="dropdown-item" onclick="openSettingsModal()">Profile & Settings</a>
                    <div class="dropdown-divider"></div>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Log out
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-item active" id="sidebarBoards" onclick="showBoards()" style="cursor: pointer;">
            <div class="main-content">
                <div class="content-wrapper">
                    <!-- Session Messages -->
                    <div style="padding: 16px 16px 0 16px;">
                </svg>
            </div>
            <span>Boards</span>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-title">Workspaces</div>
            
            @foreach($workspaces as $workspace)
                <div style="position: relative;">
                    <a href="{{ route('workspaces.show', $workspace) }}" class="workspace-item">
                        <div class="workspace-icon {{ $workspace->color }}">{{ $workspace->display_icon }}</div>
                        <span style="flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $workspace->name }}</span>
                        
                        @if($user->role === 'admin')
                            <div class="workspace-actions">
                                <a href="{{ route('workspaces.edit', $workspace) }}" class="workspace-action-btn" title="Edit Workspace" onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('workspaces.edit', $workspace) }}';">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </a>
                                <form action="{{ route('workspaces.destroy', $workspace) }}" method="POST" style="display: inline; margin: 0;" onsubmit="event.stopPropagation(); return confirm('Are you sure you want to delete this workspace? All boards will be deleted.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="workspace-action-btn delete" title="Delete Workspace">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                    </button>
                }
                .content-wrapper {
                    max-width: 1100px;
                    margin: 0 auto;
                    width: 100%;
                }
                                </form>
                            </div>
                        @endif
                    </a>
                </div>
            @endforeach
        </div>


        @if($user->role === 'admin')
            <div class="sidebar-section">
                <div class="sidebar-section-title">Admin</div>
                <div class="sidebar-item" id="sidebarUserLogin" onclick="toggleUserManagement()" style="cursor: pointer;">
                    <div class="sidebar-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                    <span>User Login</span>
                </div>
                <div class="sidebar-item" id="sidebarArchivedBoards" onclick="toggleArchivedBoards()" style="cursor: pointer;">
                    <div class="sidebar-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z"/>
                        </svg>
                    </div>
                    <span>Archived Boards</span>
                </div>
            </div>
        @endif
    </div>

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

        <!-- Your Workspaces -->
        @foreach($workspaces as $workspace)
            @if(isset($workspaceBoards[$workspace->id]) && $workspaceBoards[$workspace->id]->count() > 0)
            <div class="section">
                <div class="workspace-header">
                    <a href="{{ route('workspaces.show', $workspace) }}" style="text-decoration: none; color: inherit;">
                        <div class="workspace-header-title">
                            <div class="workspace-icon {{ $workspace->color }}">{{ $workspace->display_icon }}</div>
                            <span>{{ $workspace->name }}</span>
                        </div>
                    </a>
                    
                    @if(auth()->user()->role === 'admin')
                        <div style="display: flex; gap: 8px;">
                            <a href="{{ route('workspaces.edit', $workspace) }}" class="workspace-action-btn" title="Edit Workspace" style="border: 1px solid #38414a;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            </a>
                            <form action="{{ route('workspaces.destroy', $workspace) }}" method="POST" onsubmit="return confirm('Delete this workspace?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="workspace-action-btn delete" title="Delete Workspace" style="border: 1px solid #38414a;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                </button>
                            </form>
                        </div>
                    @endif
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
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                    </svg>
                                    Edit
                                </button>
                                <button class="board-menu-item" onclick="archiveBoard({{ $board->id }}, '{{ addslashes($board->name) }}');">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z"/>
                                    </svg>
                                    Archive
                                </button>
                                <button class="board-menu-item danger" onclick="deleteBoard({{ $board->id }}, '{{ addslashes($board->name) }}');">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                    </svg>
                                    Delete
                                </button>
                            </div>
                            @endif
                        </div>
                    @endforeach
                    @if($user->isSystemAdmin() || in_array($workspace->id, $canCreateBoardWorkspaceIds))
                    <div class="board-card create" onclick="openCreateBoardModal({{ $workspace->id }})" style="cursor: pointer;">
                        <div>Create new board</div>
                        @php
                            $remainingBoards = 10 - $workspace->boards()->where('is_archived', false)->count();
                        @endphp
                        @if($remainingBoards > 0)
                            <div style="font-size: 12px; margin-top: 4px; color: #6b778c;">{{ $remainingBoards }} remaining</div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif
        @endforeach
        </div>
        </div>
        <!-- End of boardsContent -->

        <!-- User Management Content (Hidden by default) -->
        @if($user->role === 'admin')
        <div id="userMgmtContent" class="user-mgmt-container">
            <div class="user-mgmt-header">
                <h2 class="section-header" style="margin-bottom: 0;">User Management</h2>
                <button class="btn-create" onclick="openAddUserModal()">+ Add User</button>
            </div>

            <div class="user-table-card">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                        <tr>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ ucfirst($u->role) }}</td>
                            <td>
                                <span class="status-badge {{ $u->is_active ? 'status-active' : 'status-inactive' }}">
                                    {{ $u->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="user-table-actions">
                                    @if($u->id !== Auth::id())
                                    <form action="{{ route('admin.users.toggle', $u) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn-action {{ $u->is_active ? 'btn-revoke' : 'btn-grant' }}">
                                            {{ $u->is_active ? 'Revoke Approval' : 'Grant Approval' }}
                                        </button>
                                    </form>
                                    
                                    <form action="{{ route('admin.users.destroy', $u) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-delete">Delete</button>
                                    </form>
                                    @else
                                    <span style="color: #6b778c; font-style: italic;">Current User</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <!-- Logout Form (Hidden, can be triggered via user menu) -->
    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
        @csrf
    </form>

    <!-- Create Board Modal -->
    <div class="modal-overlay" id="createBoardModal">
        <div class="create-board-modal">
            <div class="modal-header">
                <h2 class="modal-title">Create board</h2>
                <button type="button" class="close-modal-btn" onclick="closeCreateBoardModal()">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('boards.store') }}" id="boardFormModal">
                    @csrf

                    <!-- Board Preview -->
                    <div class="board-preview-modal" id="boardPreviewModal">
                        <div class="board-preview-content" id="previewTitleModal">Board title</div>
                    </div>

                    <!-- Background Selection -->
                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 14px; font-weight: 600; color: #b6c2cf; margin-bottom: 10px;">Background</div>
                        <div class="background-grid">
                            <!-- Images -->
                            <div class="background-option selected" data-type="image" data-value="https://images.unsplash.com/photo-1519681393784-d120267933ba?w=400&h=200&fit=crop">
                                <img src="https://images.unsplash.com/photo-1519681393784-d120267933ba?w=100&h=100&fit=crop" alt="Mountains">
                            </div>
                            <div class="background-option" data-type="image" data-value="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=200&fit=crop">
                                <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=100&h=100&fit=crop" alt="Sunset">
                            </div>
                            <div class="background-option" data-type="image" data-value="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=400&h=200&fit=crop">
                                <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=100&h=100&fit=crop" alt="Forest">
                            </div>
                            <div class="background-option" data-type="image" data-value="https://images.unsplash.com/photo-1448375240586-882707db888b?w=400&h=200&fit=crop">
                                <img src="https://images.unsplash.com/photo-1448375240586-882707db888b?w=100&h=100&fit=crop" alt="Road">
                            </div>
                            <div class="background-option" data-type="image" data-value="https://images.unsplash.com/photo-1518837695005-2083093ee35b?w=400&h=200&fit=crop">
                                <img src="https://images.unsplash.com/photo-1518837695005-2083093ee35b?w=100&h=100&fit=crop" alt="Ocean">
                            </div>
                            <!-- Gradients -->
                            <div class="background-option" data-type="gradient" data-value="blue">
                                <div class="background-gradient bg-blue"></div>
                            </div>
                        </div>
                        <input type="hidden" name="background_type" id="backgroundTypeModal" value="image">
                        <input type="hidden" name="background_value" id="backgroundValueModal" value="https://images.unsplash.com/photo-1519681393784-d120267933ba?w=400&h=200&fit=crop">
                    </div>

                    <!-- Board Title -->
                    <div class="modal-form-group">
                        <label for="nameModal">
                            Board title <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="nameModal" 
                            name="name" 
                            required
                            placeholder="Enter board title"
                            autofocus
                        >
                        <div class="error-message-modal" id="titleErrorModal" style="display: none;">
                            <span>⚠</span> Board title is required
                        </div>
                        @error('name')
                            <div class="error-message-modal">
                                <span>⚠</span> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Workspace Selection -->
                    <div class="modal-form-group">
                        <label for="workspace_idModal">Workspace</label>
                        <select id="workspace_idModal" name="workspace_id" required>
                            @foreach($canCreateBoardWorkspaces as $workspace)
                                <option value="{{ $workspace->id }}">
                                    {{ $workspace->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('workspace_id')
                            <div class="error-message-modal">
                                <span>⚠</span> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Visibility -->
                    <div class="modal-form-group">
                        <label for="visibilityModal">Visibility</label>
                        <select id="visibilityModal" name="visibility">
                            <option value="workspace" selected>Workspace</option>
                            <option value="private">Private</option>
                            <option value="public">Public</option>
                        </select>
                    </div>

                    <!-- Workspace Info -->
                    <div class="workspace-info-modal" id="workspaceInfoModal">
                        <p>This Workspace has <strong id="remainingBoardsCount">0</strong> boards remaining.</p>
                        <p>Free Workspaces can only have 10 open boards.</p>
                    </div>

                    @if($errors->any())
                        <div class="error-message-modal" style="margin-top: 16px;">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <button type="submit" class="btn-submit-modal" id="submitBtnModal">Create</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Add User Modal (Admin only) -->
    @if($user->role === 'admin')
    <div class="modal-overlay" id="addUserModal">
        <div class="create-board-modal admin-modal">
            <div class="modal-header">
                <h2 class="modal-title">Add New User</h2>
                <button type="button" class="close-modal-btn" onclick="closeAddUserModal()">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <div class="modal-form-group">
                        <label for="new_name">Full Name</label>
                        <input type="text" name="name" id="new_name" class="modal-input" placeholder="Enter user's name" required>
                    </div>
                    
                    <div class="modal-form-group">
                        <label for="new_email">Email Address</label>
                        <input type="email" name="email" id="new_email" class="modal-input" placeholder="Enter email address" required>
                    </div>

                    <div class="form-row">
                        <div class="modal-form-group">
                            <label for="new_password">Password</label>
                            <input type="password" name="password" id="new_password" class="modal-input" placeholder="Min 6 chars" required minlength="6">
                        </div>
                        <div class="modal-form-group">
                            <label for="new_role">Role</label>
                            <select name="role" id="new_role" class="modal-input" style="background: #22272b; cursor: pointer;">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-form-group">
                        <label class="checkbox-group">
                            <input type="checkbox" name="is_active" value="1" checked>
                            <span>Active / Approved immediately</span>
                        </label>
                    </div>

                    <div class="modal-footer" style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px;">
                        <button type="button" class="btn-cancel" onclick="closeAddUserModal()">Cancel</button>
                        <button type="submit" class="btn-create" style="padding: 8px 24px; font-size: 14px;">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- User Profile & Settings Modal -->
    <div class="modal-overlay" id="settingsModal">
        <div class="create-board-modal admin-modal" style="max-width: 500px;">
            <div class="modal-header">
                <h2 class="modal-title">Profile & Settings</h2>
                <button type="button" class="close-modal-btn" onclick="closeSettingsModal()">×</button>
            </div>
            <div class="modal-body">
                <!-- Profile Section -->
                <div style="background: #1d2125; padding: 16px; border-radius: 8px; margin-bottom: 24px; border: 1px solid #38414a;">
                    <h3 style="color: white; font-size: 14px; margin-bottom: 12px; font-weight: 600;">Account Details</h3>
                    <div style="display: grid; grid-template-columns: 100px 1fr; gap: 8px; font-size: 14px; color: #b6c2cf;">
                        <div>Name:</div>
                        <div style="color: white; font-weight: 500;">{{ auth()->user()->name }}</div>
                        <div>Email:</div>
                        <div style="color: white; font-weight: 500;">{{ auth()->user()->email }}</div>
                        <div>Role:</div>
                        <div style="color: #7c3aed; font-weight: 600;">{{ ucfirst(auth()->user()->role) }}</div>
                    </div>
                </div>

                <!-- Password Update Form -->
                <form method="POST" action="{{ route('profile.password.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <h3 style="color: white; font-size: 14px; margin-bottom: 12px; font-weight: 600;">Update Password</h3>
                    
                    <div class="modal-form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" name="current_password" id="current_password" class="modal-input" placeholder="Confirm current password" required>
                    </div>
                    
                    <div class="modal-form-group">
                        <label for="password">New Password</label>
                        <input type="password" name="password" id="password" class="modal-input" placeholder="Min 8 characters" required minlength="8">
                    </div>

                    <div class="modal-form-group">
                        <label for="password_confirmation">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="modal-input" placeholder="Repeat new password" required minlength="8">
                    </div>

                    <div class="modal-footer" style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px;">
                        <button type="button" class="btn-cancel" onclick="closeSettingsModal()">Cancel</button>
                        <button type="submit" class="btn-create" style="padding: 8px 24px; font-size: 14px;">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Archived Boards Content (Hidden by default) -->
    @if($user->role === 'admin')
    <div id="archivedBoardsContent" class="archived-boards-content" style="display: none;">
        <h2 class="section-header">Archived Boards</h2>
        <div class="boards-grid" id="archivedBoardsGrid">
            @php
                $archivedBoards = \App\Models\Board::where('is_archived', true)->with('workspace')->get();
            @endphp
            
            @if($archivedBoards->count() > 0)
                @foreach($archivedBoards as $board)
                    <div class="archived-board-wrapper" data-board-id="{{ $board->id }}">
                        <div class="board-card archived-board-card">
                            @if($board->background_type == 'image' && $board->background_value)
                                <img src="{{ $board->background_value }}" alt="{{ $board->name }}" class="board-card-image">
                            @elseif($board->background_type == 'gradient')
                                <div class="board-card-gradient {{ $board->background_value ?: 'blue' }}"></div>
                            @else
                                <div class="board-card-gradient blue"></div>
                            @endif
                            <div class="board-card-title">
                                {{ $board->name }}
                                <br><span style="font-size: 11px; font-weight: 400; opacity: 0.8;">{{ $board->workspace->name }}</span>
                            </div>
                            
                            <div class="archived-board-actions">
                                <button onclick="restoreBoard({{ $board->id }}, '{{ addslashes($board->name) }}')" class="archived-btn restore-btn">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9z"/>
                                    </svg>
                                    Restore
                                </button>
                                <button onclick="deleteBoard({{ $board->id }}, '{{ addslashes($board->name) }}')" class="archived-btn delete-btn">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z"/>
                    </svg>
                    <p class="empty-state-title">No archived boards</p>
                    <p class="empty-state-text">Archived boards will appear here</p>
                </div>
            @endif
        </div>
    </div>
    @endif

    </div>
    <!-- End of main-content -->

    <script>
        const workspaces = @json($canCreateBoardWorkspaces->mapWithKeys(function($ws) {
            return [$ws->id => [
                'name' => $ws->name,
                'remaining' => 10 - $ws->boards()->where('is_archived', false)->count()
            ]];
        }));

        function openCreateBoardModal(workspaceId = null) {
            const modal = document.getElementById('createBoardModal');
            modal.classList.add('active');
            
            if (workspaceId) {
                document.getElementById('workspace_idModal').value = workspaceId;
                updateWorkspaceInfo(workspaceId);
            } else {
                const firstWorkspaceId = document.getElementById('workspace_idModal').value;
                updateWorkspaceInfo(firstWorkspaceId);
            }
            
            document.getElementById('nameModal').focus();
        }

        function closeCreateBoardModal() {
            const modal = document.getElementById('createBoardModal');
            const form = document.getElementById('boardFormModal');
            const submitBtn = document.getElementById('submitBtnModal');
            const modalTitle = modal.querySelector('.modal-title');
            
            modal.classList.remove('active');
            form.reset();
            
            // Reset form action and method
            form.action = '{{ route("boards.store") }}';
            const methodInput = form.querySelector('input[name="_method"]');
            if (methodInput) {
                methodInput.remove();
            }
            
            // Reset button and title
            submitBtn.textContent = 'Create';
            modalTitle.textContent = 'Create board';
            
            document.getElementById('titleErrorModal').style.display = 'none';
            document.getElementById('nameModal').classList.remove('error');
            submitBtn.disabled = false;
            
            // Reset to first selected background
            document.querySelectorAll('.background-option').forEach(opt => opt.classList.remove('selected'));
            document.querySelector('.background-option').classList.add('selected');
            document.getElementById('backgroundTypeModal').value = 'image';
            document.getElementById('backgroundValueModal').value = 'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=400&h=200&fit=crop';
            updatePreview('image', 'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=400&h=200&fit=crop');
        }

        function updateWorkspaceInfo(workspaceId) {
            const workspace = workspaces[workspaceId];
            if (workspace) {
                document.getElementById('remainingBoardsCount').textContent = workspace.remaining;
            }
        }

        function updatePreview(type, value) {
            const preview = document.getElementById('boardPreviewModal');
            const title = document.getElementById('previewTitleModal').textContent || 'Board title';
            preview.innerHTML = '<div class="board-preview-content" id="previewTitleModal">' + title + '</div>';
            
            if (type === 'image') {
                preview.style.backgroundImage = `url(${value})`;
                preview.style.backgroundSize = 'cover';
                preview.style.backgroundPosition = 'center';
                preview.className = 'board-preview-modal';
            } else {
                preview.style.backgroundImage = 'none';
                preview.className = 'board-preview-modal background-gradient';
                if (value === 'blue') preview.classList.add('bg-blue');
                else if (value === 'light-blue') preview.classList.add('bg-light-blue');
                else if (value === 'medium-blue') preview.classList.add('bg-medium-blue');
                else if (value === 'purple-pink') preview.classList.add('bg-purple-pink');
                else if (value === 'pink-purple') preview.classList.add('bg-pink-purple');
            }
        }

        // Background selection
        document.querySelectorAll('.background-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.background-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                
                const type = this.dataset.type;
                const value = this.dataset.value;
                
                document.getElementById('backgroundTypeModal').value = type;
                document.getElementById('backgroundValueModal').value = value;
                
                updatePreview(type, value);
            });
        });

        // Board title preview update
        document.getElementById('nameModal').addEventListener('input', function() {
            const previewTitle = document.getElementById('previewTitleModal');
            if (previewTitle) {
                previewTitle.textContent = this.value || 'Board title';
            }
            
            // Validation
            const titleError = document.getElementById('titleErrorModal');
            const submitBtn = document.getElementById('submitBtnModal');
            if (this.value.trim() === '') {
                this.classList.add('error');
                titleError.style.display = 'flex';
                submitBtn.disabled = true;
            } else {
                this.classList.remove('error');
                titleError.style.display = 'none';
                submitBtn.disabled = false;
            }
        });

        // Workspace change - update remaining boards
        document.getElementById('workspace_idModal').addEventListener('change', function() {
            updateWorkspaceInfo(this.value);
        });

        // Form validation on submit
        document.getElementById('boardFormModal').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const nameInput = document.getElementById('nameModal');
            if (!nameInput.value.trim()) {
                nameInput.classList.add('error');
                document.getElementById('titleErrorModal').style.display = 'flex';
                nameInput.focus();
                return;
            }
            
            const form = e.target;
            const formData = new FormData(form);
            const submitBtn = document.getElementById('submitBtnModal');
            const isEdit = form.action.includes('/boards/') && formData.get('_method') === 'PUT';
            
            submitBtn.disabled = true;
            submitBtn.textContent = isEdit ? 'Updating...' : 'Creating...';
            
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: isEdit ? 'Board updated successfully' : 'Board created successfully',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        background: '#22272b',
                        color: '#b6c2cf'
                    });
                    
                    closeCreateBoardModal();
                    
                    if (isEdit) {
                        // Update board card in DOM
                        const boardId = data.board.id;
                        const boardCard = document.querySelector(`#boardMenu${boardId}`).closest('.board-card-wrapper');
                        if (boardCard) {
                            const boardLink = boardCard.querySelector('.board-card');
                            const titleElement = boardLink.querySelector('.board-card-title');
                            
                            // Update title
                            titleElement.childNodes[0].textContent = data.board.name;
                            
                            // Update background
                            const existingBg = boardLink.querySelector('.board-card-image, .board-card-gradient');
                            if (existingBg) {
                                existingBg.remove();
                            }
                            
                            if (data.board.background_type === 'image') {
                                const img = document.createElement('img');
                                img.src = data.board.background_value;
                                img.alt = data.board.name;
                                img.className = 'board-card-image';
                                boardLink.insertBefore(img, titleElement);
                            } else if (data.board.background_type === 'gradient') {
                                const gradient = document.createElement('div');
                                gradient.className = `board-card-gradient ${data.board.background_value || 'blue'}`;
                                boardLink.insertBefore(gradient, titleElement);
                            }
                        }
                    } else {
                        // Reload for new board
                        setTimeout(() => location.reload(), 1000);
                    }
                } else {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: data.error || 'An error occurred',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        background: '#22272b',
                        color: '#b6c2cf'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'An error occurred',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    background: '#22272b',
                    color: '#b6c2cf'
                });
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = isEdit ? 'Update Board' : 'Create';
            });
        });

        // Close modal on overlay click
        document.getElementById('createBoardModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCreateBoardModal();
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCreateBoardModal();
            }
        });

        // Initialize workspace info on load
        document.addEventListener('DOMContentLoaded', function() {
            const workspaceSelect = document.getElementById('workspace_idModal');
            if (workspaceSelect) {
                updateWorkspaceInfo(workspaceSelect.value);
            }
        });

        // Board Menu Functions
        function toggleBoardMenu(boardId) {
            const menu = document.getElementById('boardMenu' + boardId);
            const allMenus = document.querySelectorAll('.board-menu-dropdown');
            
            // Close all other menus
            allMenus.forEach(m => {
                if (m.id !== 'boardMenu' + boardId) {
                    m.classList.remove('active');
                }
            });
            
            menu.classList.toggle('active');
            
            // Close when clicking outside
            if (menu.classList.contains('active')) {
                const closeHandler = (e) => {
                    if (!menu.contains(e.target) && !e.target.closest('.board-menu-btn')) {
                        menu.classList.remove('active');
                        document.removeEventListener('click', closeHandler);
                    }
                };
                setTimeout(() => document.addEventListener('click', closeHandler), 0);
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        
        function editBoard(boardId, name, description, bgType, bgValue, workspaceId) {
            // Close all menus
            document.querySelectorAll('.board-menu-dropdown').forEach(m => m.classList.remove('active'));
            
            // Open create board modal in edit mode
            const modal = document.getElementById('createBoardModal');
            const form = document.getElementById('boardFormModal');
            const titleInput = document.getElementById('nameModal');
            const workspaceSelect = document.getElementById('workspace_idModal');
            const submitBtn = document.getElementById('submitBtnModal');
            const modalTitle = modal.querySelector('.modal-title');
            
            // Reset form first
            form.action = `/boards/${boardId}`;
            
            // Remove any existing method input
            const existingMethod = form.querySelector('input[name="_method"]');
            if (existingMethod) {
                existingMethod.remove();
            }
            
            // Add PUT method
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PUT';
            form.appendChild(methodInput);
            
            // Fill form with board data
            titleInput.value = name;
            workspaceSelect.value = workspaceId;
            submitBtn.textContent = 'Update Board';
            modalTitle.textContent = 'Edit board';
            
            // Update preview
            document.getElementById('previewTitleModal').textContent = name || 'Board title';
            
            // Select background
            if (bgType === 'gradient') {
                const bgOption = document.querySelector(`.background-option[data-type="gradient"][data-value="${bgValue}"]`);
                if (bgOption) {
                    document.querySelectorAll('.background-option').forEach(opt => opt.classList.remove('selected'));
                    bgOption.classList.add('selected');
                    document.getElementById('backgroundTypeModal').value = 'gradient';
                    document.getElementById('backgroundValueModal').value = bgValue;
                }
            } else if (bgType === 'image') {
                const bgOption = document.querySelector(`.background-option[data-type="image"][data-value="${bgValue}"]`);
                if (bgOption) {
                    document.querySelectorAll('.background-option').forEach(opt => opt.classList.remove('selected'));
                    bgOption.classList.add('selected');
                    document.getElementById('backgroundTypeModal').value = 'image';
                    document.getElementById('backgroundValueModal').value = bgValue;
                }
            }
            
            modal.classList.add('active');
        }
        
        function archiveBoard(boardId, boardName) {
            // Close all menus
            document.querySelectorAll('.board-menu-dropdown').forEach(m => m.classList.remove('active'));
            
            Swal.fire({
                title: 'Archive board?',
                text: `Are you sure you want to archive "${boardName}"? You can restore it later from the archived boards section.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0079bf',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Archive',
                cancelButtonText: 'Cancel',
                background: '#22272b',
                color: '#b6c2cf',
                customClass: {
                    popup: 'swal2-dark'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/boards/${boardId}/archive`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Board archived successfully',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                background: '#22272b',
                                color: '#b6c2cf'
                            });
                            
                            // Remove board card from DOM
                            const boardCard = document.querySelector(`#boardMenu${boardId}`).closest('.board-card-wrapper');
                            if (boardCard) {
                                boardCard.style.transition = 'opacity 0.3s';
                                boardCard.style.opacity = '0';
                                setTimeout(() => boardCard.remove(), 300);
                            }
                        }
                    });
                }
            });
        }
        
        function deleteBoard(boardId, boardName) {
            // Close all menus
            document.querySelectorAll('.board-menu-dropdown').forEach(m => m.classList.remove('active'));
            
            Swal.fire({
                title: 'Delete board?',
                text: `Are you sure you want to permanently delete "${boardName}"? This action cannot be undone!`,
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                background: '#22272b',
                color: '#b6c2cf',
                customClass: {
                    popup: 'swal2-dark'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/boards/${boardId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Board deleted successfully',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                background: '#22272b',
                                color: '#b6c2cf'
                            });
                            
                            // Check if we're in archived boards view or regular boards view
                            const archivedWrapper = document.querySelector(`.archived-board-wrapper[data-board-id="${boardId}"]`);
                            const regularWrapper = document.querySelector(`#boardMenu${boardId}`)?.closest('.board-card-wrapper');
                            
                            if (archivedWrapper) {
                                // Deleting from archived section
                                archivedWrapper.style.transition = 'opacity 0.3s, transform 0.3s';
                                archivedWrapper.style.opacity = '0';
                                archivedWrapper.style.transform = 'scale(0.9)';
                                setTimeout(() => {
                                    archivedWrapper.remove();
                                    
                                    // Check if no more archived boards, show empty state
                                    const remainingBoards = document.querySelectorAll('.archived-board-wrapper');
                                    if (remainingBoards.length === 0) {
                                        const grid = document.getElementById('archivedBoardsGrid');
                                        grid.innerHTML = `
                                            <div class="empty-state">
                                                <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z"/>
                                                </svg>
                                                <p class="empty-state-title">No archived boards</p>
                                                <p class="empty-state-text">Archived boards will appear here</p>
                                            </div>
                                        `;
                                    }
                                }, 300);
                            } else if (regularWrapper) {
                                // Deleting from regular boards view
                                regularWrapper.style.transition = 'opacity 0.3s';
                                regularWrapper.style.opacity = '0';
                                setTimeout(() => regularWrapper.remove(), 300);
                            }
                        }
                    });
                }
            });
        }
    </script>
    <script>
        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('active');
            
            // Close when clicking outside
            if (dropdown.classList.contains('active')) {
                const closeHandler = (e) => {
                    const avatar = document.getElementById('userAvatar');
                    if (avatar && !avatar.contains(e.target)) {
                        dropdown.classList.remove('active');
                        document.removeEventListener('click', closeHandler);
                    }
                };
                setTimeout(() => document.addEventListener('click', closeHandler), 0);
            }
        }

        /* View Toggling */
        function showBoards() {
            document.getElementById('boardsContent').style.display = 'block';
            if(document.getElementById('userMgmtContent')) {
                document.getElementById('userMgmtContent').style.display = 'none';
            }
            if(document.getElementById('archivedBoardsContent')) {
                document.getElementById('archivedBoardsContent').style.display = 'none';
            }
            document.querySelectorAll('.sidebar-item').forEach(el => el.classList.remove('active'));
            document.getElementById('sidebarBoards').classList.add('active');
            localStorage.setItem('dashboard_view', 'boards');
        }
        
        function toggleUserManagement() {
            document.getElementById('boardsContent').style.display = 'none';
            document.getElementById('userMgmtContent').style.display = 'block';
            if(document.getElementById('archivedBoardsContent')) {
                document.getElementById('archivedBoardsContent').style.display = 'none';
            }
            document.querySelectorAll('.sidebar-item').forEach(el => el.classList.remove('active'));
            document.getElementById('sidebarUserLogin').classList.add('active');
            localStorage.setItem('dashboard_view', 'users');
        }
        
        function toggleArchivedBoards() {
            document.getElementById('boardsContent').style.display = 'none';
            if(document.getElementById('userMgmtContent')) {
                document.getElementById('userMgmtContent').style.display = 'none';
            }
            document.getElementById('archivedBoardsContent').style.display = 'block';
            document.querySelectorAll('.sidebar-item').forEach(el => el.classList.remove('active'));
            document.getElementById('sidebarArchivedBoards').classList.add('active');
            localStorage.setItem('dashboard_view', 'archived');
        }
        
        function restoreBoard(boardId, boardName) {
            Swal.fire({
                title: 'Restore board?',
                text: `Restore "${boardName}" to active boards?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0079bf',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Restore',
                cancelButtonText: 'Cancel',
                background: '#22272b',
                color: '#b6c2cf',
                customClass: {
                    popup: 'swal2-dark'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/boards/${boardId}/restore`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Board restored successfully',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                background: '#22272b',
                                color: '#b6c2cf'
                            });
                            
                            // Remove board card from archived section with animation
                            const boardWrapper = document.querySelector(`.archived-board-wrapper[data-board-id="${boardId}"]`);
                            if (boardWrapper) {
                                boardWrapper.style.transition = 'opacity 0.3s, transform 0.3s';
                                boardWrapper.style.opacity = '0';
                                boardWrapper.style.transform = 'scale(0.9)';
                                setTimeout(() => {
                                    boardWrapper.remove();
                                    
                                    // Check if no more archived boards, show empty state
                                    const remainingBoards = document.querySelectorAll('.archived-board-wrapper');
                                    if (remainingBoards.length === 0) {
                                        const grid = document.getElementById('archivedBoardsGrid');
                                        grid.innerHTML = `
                                            <div class="empty-state">
                                                <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z"/>
                                                </svg>
                                                <p class="empty-state-title">No archived boards</p>
                                                <p class="empty-state-text">Archived boards will appear here</p>
                                            </div>
                                        `;
                                    }
                                }, 300);
                            }
                        }
                    });
                }
            });
        }

        /* Load saved view on page load */
        document.addEventListener('DOMContentLoaded', function() {
            const savedView = localStorage.getItem('dashboard_view');
            if (savedView === 'users' && document.getElementById('userMgmtContent')) {
                toggleUserManagement();
            } else if (savedView === 'archived' && document.getElementById('archivedBoardsContent')) {
                toggleArchivedBoards();
            }
        });

        /* Add User Modal */
        function openAddUserModal() {
            document.getElementById('addUserModal').classList.add('active');
        }

        function closeAddUserModal() {
            document.getElementById('addUserModal').classList.remove('active');
        }

        // Restore view on load
        document.addEventListener('DOMContentLoaded', function() {
            const savedView = localStorage.getItem('dashboard_view');
            if (savedView === 'users' && document.getElementById('userMgmtContent')) {
                showUserManagement();
            } else {
                showBoards();
            }
        });

        // Add User Modal click listeners
        if (document.getElementById('addUserModal')) {
            document.getElementById('addUserModal').addEventListener('click', function(e) {
                if (e.target === this) closeAddUserModal();
            });
        }

        /* Settings Modal */
        function openSettingsModal() {
            document.getElementById('settingsModal').classList.add('active');
            document.getElementById('userDropdown').classList.remove('active');
        }

        function closeSettingsModal() {
            document.getElementById('settingsModal').classList.remove('active');
        }

        if (document.getElementById('settingsModal')) {
            document.getElementById('settingsModal').addEventListener('click', function(e) {
                if (e.target === this) closeSettingsModal();
            });
        }
    </script>
</body>
</html>