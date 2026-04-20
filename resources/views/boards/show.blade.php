<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <script>window.BASE_URL = '{{ rtrim(config('app.url'), '/') }}';</script>
    <title>{{ $board->name }} - Trello</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #1d2125;
            color: #b6c2cf;
            height: 100vh;
            overflow: hidden;
        }

        /* Top Header Bar */
        .top-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 40px;
            background: rgba(37, 42, 47, 0.95);
            border-bottom: 1px solid #38414a;
            display: flex;
            align-items: center;
            padding: 0 16px;
            z-index: 1000;
        }
        .header-left { display: flex; align-items: center; gap: 12px; }
        .grid-icon { width: 20px; height: 20px; cursor: pointer; color: #b6c2cf; }
        .trello-logo-header {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        .trello-logo-small {
            width: 24px;
            height: 24px;
            background: #0052cc;
            border-radius: 4px;
            position: relative;
        }
        .trello-logo-small::before,
        .trello-logo-small::after {
            content: '';
            position: absolute;
            width: 10px;
            height: 4px;
            background: white;
            border-radius: 1px;
        }
        .trello-logo-small::before { top: 6px; left: 7px; }
        .trello-logo-small::after { bottom: 6px; left: 7px; }
        .trello-text-header {
            font-size: 18px;
            font-weight: 700;
            color: white;
            letter-spacing: -0.3px;
        }
        .search-bar {
            flex: 1;
            max-width: 600px;
            margin: 0 auto;
            position: relative;
            overflow: visible;
        }
        .search-input {
            width: 100%;
            height: 32px;
            padding: 8px 12px 8px 36px;
            background: #22272b;
            border: 1px solid #38414a;
            border-radius: 4px;
            color: #b6c2cf;
            font-size: 14px;
        }
        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            color: #6b778c;
        }
        .header-right {
            display: flex;
            align-items: center;
            gap: 12px;
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
        .header-icon:hover { background: #22272b; }
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
            text-align: left;
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
        /* Board Header Bar */
        .board-header {
            position: fixed;
            top: 40px;
            left: 0;
            right: 0;
            height: 48px;
            background: rgba(37, 42, 47, 0.95);
            border-bottom: 1px solid #38414a;
            display: flex;
            align-items: center;
            padding: 0 16px;
            z-index: 999;
        }
        .board-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .board-name {
            font-size: 16px;
            font-weight: 600;
            color: white;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
        }
        .board-name:hover {
            background: #22272b;
        }
        .board-header-center {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: 20px;
        }
        .board-icon-btn {
            width: 32px;
            height: 32px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #b6c2cf;
        }
        .board-icon-btn:hover {
            background: #22272b;
        }
        .board-header-right {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }
        .btn-share {
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
        /* Board Content */
        .board-content {
            margin-top: 88px;
            padding: 8px;
            min-height: calc(100vh - 88px);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            overflow-x: auto !important; /* The only horizontal scroll */
            overflow-y: hidden; /* Cards scroll inside lists, not board */
            display: block;
            white-space: nowrap; /* Keep lists on one line */
        }
        .board-content-gradient {
            background: linear-gradient(135deg, #0052cc 0%, #0065ff 100%);
        }
        .lists-container {
            display: inline-flex; /* Use inline-flex to grow with content */
            gap: 16px;
            align-items: flex-start;
            padding-bottom: 8px;
            min-height: calc(100vh - 120px);
            overflow: visible !important; /* Rely on board-content */
        }
        .list {
            background: #22272b;
            border-radius: 8px;
            width: 272px;
            min-width: 272px;
            max-height: calc(100vh - 120px);
            display: flex;
            flex-direction: column;
            cursor: grab;
            user-select: none;
        }
        .list:active {
            cursor: grabbing;
        }
        .list-dragging {
            opacity: 0.5;
            cursor: grabbing !important;
            transform: rotate(2deg) scale(1.02);
            z-index: 9999;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.6);
        }
        .list-ghost {
            background: rgba(0, 82, 204, 0.15);
            border: 2px dashed #0052cc;
            opacity: 0.6;
        }
        .list-chosen {
            cursor: grabbing !important;
        }
        .list-header {
            padding: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .list-title {
            font-size: 14px;
            font-weight: 600;
            color: white;
            flex: 1;
            cursor: grab;
        }
        .list-title:active {
            cursor: grabbing;
        }
        .list-menu {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #b6c2cf;
            position: relative;
            flex-shrink: 0;
        }
        .list-menu:hover {
            background: #2c333a;
        }
        .list-options-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 4px;
            background: #22272b;
            border: 1px solid #38414a;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
            min-width: 180px;
            z-index: 100;
            padding: 4px 0;
        }
        .list-options-dropdown .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            color: #b6c2cf;
            font-size: 14px;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            transition: background 0.15s;
        }
        .list-options-dropdown .dropdown-item:hover {
            background: #2c333a;
        }
        .list-options-dropdown .dropdown-item.danger {
            color: #e74c3c;
        }
        .list-options-dropdown .dropdown-item svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }
        .list-options-overlay {
            position: fixed;
            inset: 0;
            z-index: 99;
        }
        .list-cards {
            flex: 1;
            overflow-y: auto;
            padding: 0 12px 12px;
        }
        .card {
            background: #22272b;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 8px;
            cursor: pointer;
            border: 1px solid #38414a;
            transition: all 0.2s ease;
            user-select: none;
            position: relative;
            display: block;
            text-decoration: none;
            color: inherit;
        }
        .card:active {
            cursor: grabbing;
        }
        .card:hover {
            background: #2c333a;
            border-color: #45505c;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .card-dragging {
            opacity: 0.5;
            cursor: grabbing !important;
            transform: rotate(2deg) scale(1.02);
            z-index: 9999;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.6);
        }
        .card-ghost {
            background: rgba(0, 82, 204, 0.15);
            border: 2px dashed #0052cc;
            opacity: 0.6;
        }
        .card-chosen {
            cursor: grabbing !important;
        }
        @keyframes pulse {
            0%, 100% {
                opacity: 0.4;
            }
            50% {
                opacity: 0.7;
            }
        }
        .list.drag-over {
            background: rgba(0, 82, 204, 0.1);
            border: 2px solid #0052cc;
            transition: background 0.15s ease, border 0.15s ease;
        }
        .list-cards.drag-over {
            background: rgba(0, 82, 204, 0.05);
            border-radius: 4px;
            min-height: 50px;
            transition: background 0.15s ease;
        }
        .list-cards:empty::before {
            content: 'Drop card here';
            display: block;
            padding: 20px;
            text-align: center;
            color: #9fadbc;
            font-size: 14px;
            opacity: 0.5;
        }
        .list-cards.drag-over:empty::before {
            color: #0052cc;
            opacity: 1;
        }
        .card.moving {
            transition: transform 0.25s cubic-bezier(0.25, 0.46, 0.45, 0.94), 
                       opacity 0.25s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .card-cover {
            height: 160px;
            margin: -12px -12px 8px -12px;
            border-radius: 6px 6px 0 0;
            background-size: cover;
            background-position: center top;
            background-repeat: no-repeat;
            overflow: hidden;
        }
        .card-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center top;
            display: block;
            border-radius: 6px 6px 0 0;
        }
        .card:not(.has-cover) .card-cover {
            display: none;
        }
        .card-labels {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-bottom: 8px;
        }
        .card-labels:empty {
            display: none;
        }
        .card-label {
            display: inline-block;
            height: 8px;
            min-width: 40px;
            max-width: 80px;
            border-radius: 4px;
            flex-shrink: 0;
        }
        .card-content {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
            width: 100%;
        }
        .card-title {
            font-size: 14px;
            color: #b6c2cf;
            word-wrap: break-word;
            flex: 1;
            line-height: 1.4;
            margin: 0;
        }
        .card-menu-btn {
            opacity: 0;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: opacity 0.2s ease, background 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 24px;
            height: 24px;
        }
        .card:hover .card-menu-btn {
            opacity: 1;
        }
        .card-menu-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        .card-menu-btn svg {
            width: 16px;
            height: 16px;
        }
        .card-options-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 4px;
            background: #22272b;
            border: 1px solid #38414a;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
            min-width: 200px;
            z-index: 100;
            padding: 4px 0;
        }
        .card-options-dropdown .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            color: #b6c2cf;
            font-size: 14px;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            transition: background 0.15s;
        }
        .card-options-dropdown .dropdown-item:hover {
            background: #2c333a;
        }
        .card-options-dropdown .dropdown-item.danger {
            color: #e74c3c;
        }
        .card-options-dropdown .dropdown-item svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }
        .card-options-overlay {
            position: fixed;
            inset: 0;
            z-index: 99;
        }
        .labels-modal .label-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            margin: 4px;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .labels-modal .label-chip.selected {
            border-color: #fff;
            box-shadow: 0 0 0 1px rgba(0,0,0,0.2);
        }
        .labels-modal .label-options { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
        .cover-section {
            margin-bottom: 24px;
        }
        .cover-section-title {
            font-size: 12px;
            font-weight: 600;
            color: #9fadbc;
            text-transform: uppercase;
            margin-bottom: 12px;
        }
        .cover-size-options {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }
        .cover-size-option {
            flex: 1;
            border: 2px solid #38414a;
            border-radius: 6px;
            padding: 8px;
            cursor: pointer;
            background: #2c333a;
            transition: all 0.2s;
        }
        .cover-size-option:hover {
            border-color: #45505c;
        }
        .card-badges {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
            color: #9fadbc;
            font-size: 12px;
        }
        .card-badge {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 2px 4px;
            border-radius: 3px;
        }
        .card-badge.due-date {
            background: transparent;
            color: #9fadbc;
        }
        .card-badge.due-date.has-date {
            background: #3d444d;
            color: #b6c2cf;
        }
        .card-badge.due-date.overdue {
            background: #eb5a46;
            color: #fff;
        }
        .card-badge.due-date.completed {
            background: #0052cc;
            color: #fff;
        }
        .card-badge svg {
            width: 16px;
            height: 16px;
        }
        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 4px;
            width: 100%;
        }
        .card-members {
            display: flex;
            align-items: center;
            flex-direction: row-reverse;
        }
        .card-member-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #0052cc;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
            border: 2px solid #22272b;
            margin-left: -8px;
            text-transform: uppercase;
        }
        .card-member-avatar:last-child {
            margin-left: 0;
        }

        .cover-size-option.selected {
            border-color: #0052cc;
            background: rgba(0, 82, 204, 0.1);
        }
        .cover-size-preview {
            width: 100%;
            height: 60px;
            border-radius: 4px;
            background: #22272b;
            margin-bottom: 8px;
            position: relative;
            overflow: hidden;
        }
        .cover-size-preview.full {
            background-size: cover;
            background-position: center;
        }
        .cover-size-preview.thumbnail {
            display: flex;
            gap: 8px;
        }
        .cover-size-preview.thumbnail .preview-image {
            width: 40px;
            height: 40px;
            border-radius: 4px;
            background-size: cover;
            background-position: center;
        }
        .cover-size-preview.thumbnail .preview-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .cover-size-preview.thumbnail .preview-line {
            height: 8px;
            background: #38414a;
            border-radius: 2px;
        }
        .cover-size-label {
            font-size: 12px;
            color: #b6c2cf;
            text-align: center;
        }
        .cover-remove-btn {
            width: 100%;
            background: #2c333a;
            border: 1px solid #38414a;
            color: #b6c2cf;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 24px;
        }
        .cover-remove-btn:hover {
            background: #38414a;
        }
        .cover-colors-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }
        .cover-color-option {
            aspect-ratio: 1;
            border-radius: 6px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
        }
        .cover-color-option:hover {
            transform: scale(1.05);
        }
        .cover-color-option.selected {
            border-color: #fff;
            box-shadow: 0 0 0 2px #0052cc;
        }
        .cover-upload-btn {
            width: 100%;
            background: #2c333a;
            border: 1px solid #38414a;
            color: #b6c2cf;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .cover-upload-btn:hover {
            background: #38414a;
        }
        .cover-tip {
            font-size: 12px;
            color: #9fadbc;
            margin-bottom: 24px;
        }
        .cover-unsplash-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }
        .cover-unsplash-item {
            aspect-ratio: 1;
            border-radius: 6px;
            cursor: pointer;
            border: 2px solid transparent;
            overflow: hidden;
            position: relative;
            background: #2c333a;
        }
        .cover-unsplash-item:hover {
            border-color: #45505c;
        }
        .cover-unsplash-item.selected {
            border-color: #0052cc;
            box-shadow: 0 0 0 2px #0052cc;
        }
        .cover-unsplash-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .cover-search-btn {
            width: 100%;
            background: #2c333a;
            border: 1px solid #38414a;
            color: #b6c2cf;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        .cover-search-btn:hover {
            background: #38414a;
        }
        .add-card {
            padding: 8px 12px;
            color: #9fadbc;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            margin: 0 12px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .add-card:hover {
            background: #2c333a;
            color: #b6c2cf;
        }
        .add-list {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            width: 272px;
            min-width: 272px;
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: white;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .add-list:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        .add-list-form {
            background: #22272b;
            border-radius: 8px;
            width: 272px;
            min-width: 272px;
            padding: 12px;
        }
        .add-list-form input {
            width: 100%;
            padding: 8px 12px;
            background: #1d2125;
            border: 2px solid #0052cc;
            border-radius: 4px;
            color: #b6c2cf;
            font-size: 14px;
            font-family: inherit;
            margin-bottom: 8px;
        }
        .add-list-form input:focus {
            outline: none;
        }
        .add-list-form-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .btn-add-list {
            background: #0052cc;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            font-family: inherit;
        }
        .btn-add-list:hover {
            background: #0065ff;
        }
        .btn-cancel-list {
            background: none;
            border: none;
            color: #b6c2cf;
            cursor: pointer;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
        }
        .btn-cancel-list:hover {
            background: #2c333a;
        }
        /* SweetAlert2 Dark Theme */
        .swal2-popup.swal2-dark {
            background: #22272b !important;
            color: #b6c2cf !important;
        }
        .swal2-title {
            color: #b6c2cf !important;
        }
        .swal2-content {
            color: #9fadbc !important;
        }
        .swal2-confirm.swal2-confirm-dark {
            background: #e74c3c !important;
            color: white !important;
        }
        .swal2-confirm.swal2-confirm-dark:hover {
            background: #c0392b !important;
        }
        .swal2-cancel.swal2-cancel-dark {
            background: #6c757d !important;
            color: white !important;
        }
        .swal2-cancel.swal2-cancel-dark:hover {
            background: #5a6268 !important;
        }
        .swal2-icon.swal2-warning {
            border-color: #f39c12 !important;
            color: #f39c12 !important;
        }
        .swal2-toast.swal2-toast-dark {
            background: #22272b !important;
            color: #b6c2cf !important;
            border: 1px solid #38414a !important;
        }
        .swal2-toast .swal2-title {
            color: #b6c2cf !important;
        }
        .swal2-toast .swal2-success {
            border-color: #0052cc !important;
            color: #0052cc !important;
        }
        .swal2-toast .swal2-error {
            border-color: #e74c3c !important;
            color: #e74c3c !important;
        }
        /* Card Modal */
        .card-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 3000;
        }
        .card-modal-overlay.active {
            display: flex;
        }
        .card-modal {
            background: #22272b;
            border-radius: 8px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }
        .card-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            border-bottom: 1px solid #38414a;
        }
        .card-modal-title {
            font-size: 14px;
            font-weight: 600;
            color: white;
        }
        .card-modal-close {
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
        .card-modal-close:hover {
            background: #2c333a;
        }
        .card-modal-body {
            padding: 16px;
        }
        .card-form-group {
            margin-bottom: 12px;
        }
        .card-form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #b6c2cf;
            margin-bottom: 8px;
        }
        .card-form-group input,
        .card-form-group textarea {
            width: 100%;
            padding: 10px 12px;
            background: #1d2125;
            border: 2px solid #38414a;
            border-radius: 4px;
            color: #b6c2cf;
            font-size: 14px;
            font-family: inherit;
        }
        .card-form-group input:focus,
        .card-form-group textarea:focus {
            outline: none;
            border-color: #0052cc;
        }
        .card-form-group textarea {
            resize: vertical;
            min-height: 60px;
        }
        .card-modal-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            border-top: 1px solid #38414a;
        }
        .btn-add-card-modal {
            background: #0052cc;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }
        .btn-add-card-modal:hover {
            background: #0065ff;
        }
        .btn-tip {
            background: #7c3aed;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            font-family: inherit;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-tip:hover {
            background: #8b5cf6;
        }

        /* Custom Webkit Scrollbars for Board and Lists */
        .board-content, .lists-container, .list-cards {
            scrollbar-color: rgba(255, 255, 255, 0.4) rgba(0, 0, 0, 0.15);
            scrollbar-width: thin;
        }
        
        /* Subtle scrollbars that don't distact */
        .board-content::-webkit-scrollbar {
            height: 12px;
        }
        .board-content::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }
        .board-content::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border: 3px solid transparent;
            background-clip: padding-box;
            border-radius: 10px;
        }
        .board-content::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
            border: 3px solid transparent;
            background-clip: padding-box;
        }

        .list-cards::-webkit-scrollbar {
            width: 8px;
        }
        .list-cards::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .board-content {
            user-select: none;
            -webkit-user-select: none;
        }

        .board-content.grabbing-active {
            cursor: grabbing !important;
        }
        
        .board-content.can-grab {
            cursor: grab;
        }
         .archived-sidebar {
            position: fixed;
            top: 0;
            right: -340px;
            width: 340px;
            height: 100vh;
            background: #22272b;
            box-shadow: -2px 0 8px rgba(0, 0, 0, 0.3);
            z-index: 2000;
            transition: right 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        .archived-sidebar.active {
            right: 0;
        }
        
        .archived-sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1999;
            display: none;
        }
        
        .archived-sidebar-overlay.active {
            display: block;
        }
        
        .archived-sidebar-header {
            padding: 16px;
            border-bottom: 1px solid #38414a;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .archived-sidebar-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: #b6c2cf;
            margin: 0;
        }
        
        .archived-sidebar-close {
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
            transition: background 0.2s;
        }
        
        .archived-sidebar-close:hover {
            background: #2c333a;
        }
        
        .archived-sidebar-body {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
        }
        
        .archived-card-item {
            background: #2c333a;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 12px;
            border: 1px solid #38414a;
        }
        
        .archived-card-content {
            margin-bottom: 12px;
        }
        
        .archived-card-title {
            font-size: 14px;
            font-weight: 500;
            color: #b6c2cf;
            margin-bottom: 6px;
            word-wrap: break-word;
        }
        
        .archived-card-meta {
            font-size: 12px;
            color: #9fadbc;
        }
        
        .archived-card-meta strong {
            color: #b6c2cf;
        }
        
        .archived-card-time {
            margin-left: 8px;
            color: #6b778c;
        }
        
        .archived-card-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-restore,
        .btn-delete-archived {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .btn-restore {
            background: #0052cc;
            color: white;
        }
        
        .btn-restore:hover {
            background: #0065ff;
        }
        
        .btn-delete-archived {
            background: #2c333a;
            color: #b6c2cf;
            border: 1px solid #38414a;
        }
        
        .btn-delete-archived:hover {
            background: #38414a;
            color: #e74c3c;
            border-color: #e74c3c;
        }
        
        .no-archived-cards,
        .error-message {
            text-align: center;
            padding: 32px 16px;
            color: #9fadbc;
            font-size: 14px;
        }
        
        .error-message {
            color: #e74c3c;
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
            <a href="{{ route('dashboard') }}" class="trello-logo-header">
                <div class="trello-logo-small"></div>
                <span class="trello-text-header">Trello</span>
            </a>
        </div>
        <!-- <div class="search-bar">
            <svg class="search-icon" viewBox="0 0 16 16" fill="currentColor">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
            </svg>
            <input type="text" class="search-input" placeholder="Search">
        </div> -->
        <div class="search-bar" style="position: relative;">
    <svg class="search-icon" viewBox="0 0 16 16" fill="currentColor">
        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
    </svg>
    <input type="text" id="boardSearchInput" class="search-input" placeholder="Search" oninput="filterBoards()">
    
    <div id="searchDropdown" style="display:none; position: fixed; background: #22272b; border: 1px solid #454f59; border-radius: 4px; z-index: 9999; max-height: 400px; overflow-y: auto; box-shadow: 0 8px 24px rgba(0,0,0,0.5); min-width: 300px;">
        </div>
</div>
        <div class="header-right">
            @include('partials.notification-bell')
                <div class="user-avatar-header" id="userAvatar" onclick="toggleUserDropdown()">
                {{ substr(auth()->user()->name, 0, 2) }}
                <div class="user-dropdown" id="userDropdown">
                    <div class="dropdown-user-info">
                        <strong>{{ auth()->user()->name }}</strong><br>
                        {{ auth()->user()->email }}
                    </div>
                    <a href="{{ route('profile.show') }}" class="dropdown-item">Profile & Settings</a>
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

    <!-- Board Header -->
    <div class="board-header">
        <div class="board-header-left">
            <div class="board-name">{{ $board->name }}</div>
        </div>
        
 

        <div class="board-header-right">
            <!-- Members Button with Dropdown (Only for Admin and Owner) -->
            @php
                $isAdmin = Auth::user()->isSystemAdmin();
                $workspaceOwnerId = $board->workspace->users()->wherePivot('role', 'owner')->first()?->id;
                $isOwner = Auth::id() === $workspaceOwnerId;
                $canManageMembers = $isAdmin || $isOwner;
            @endphp
            
            <div style="display:flex; align-items:center; gap:6px; margin-right:8px;">
                <!-- Shared member avatars -->
                @php
                    $sharedMembers = $board->sharedUsers()
                        ->where('users.is_active', true)
                        ->where('users.role', '!=', 'admin')
                        ->where('users.id', '!=', $workspaceOwnerId)
                        ->get();
                @endphp
                <div id="boardMemberAvatars" style="display:flex; align-items:center;">
                    @foreach($sharedMembers->take(5) as $member)
                    <div title="{{ $member->name }}" style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg, #0052cc, #0047b2);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;border:2px solid rgba(0,0,0,0.3);margin-left:-6px;cursor:default;">
                        {{ strtoupper(substr($member->name,0,1)) }}
                    </div>
                    @endforeach
                    @if($sharedMembers->count() > 5)
                    <div title="{{ $sharedMembers->count() - 5 }} more" style="width:32px;height:32px;border-radius:50%;background:#3d444d;color:#9fadbc;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;border:2px solid rgba(0,0,0,0.3);margin-left:-6px;">
                        +{{ $sharedMembers->count() - 5 }}
                    </div>
                    @endif
                </div>

                <div style="position: relative; display: inline-block;">
                    <button onclick="toggleMembersDropdown()" style="padding: 8px 16px; background: rgba(255,255,255,0.2); color: white; border: none; border-radius: 3px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.2s; white-space: nowrap; display: flex; align-items: center; gap: 8px;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                        Share
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M7 10l5 5 5-5z"/></svg>
                    </button>
                    <div id="membersDropdown" style="display: none; position: absolute; top: 45px; right: 0; background: #22272b; border: 1px solid #3c444d; border-radius: 8px; width: 350px; box-shadow: 0 8px 16px rgba(0,0,0,0.5); z-index: 999; padding: 0;">
                        <div style="position: absolute; top: -6px; right: 15px; width: 10px; height: 10px; background: #22272b; border-left: 1px solid #3c444d; border-top: 1px solid #3c444d; transform: rotate(45deg);"></div>
                        <!-- Share Link Section -->
                        <div style="padding: 16px; border-bottom: 1px solid #3c444d; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                            <div style="font-size: 12px; font-weight: 600; color: #9fadbc; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.5px;">Share Link</div>
                            
                            <div style="display: flex; align-items: flex-start; gap: 12px;">
                                <div style="background: #2c333a; padding: 8px; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#b6c2cf" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                                    </svg>
                                </div>

                                <div style="display: flex; flex-direction: column; gap: 8px; flex: 1;">
                                    <span style="font-size: 14px; color: #b6c2cf;">Anyone with the link can join as a member</span>
                                    <div id="shareLinkContainer" style="display: none;">
                                        <input type="text" id="shareLinkInput" readonly style="width: 100%; padding: 10px 12px; background: #2c333a; border: 1px solid #3c444d; border-radius: 4px; color: #b6c2cf; font-size: 12px; box-sizing: border-box; margin-bottom: 8px;">
                                        <div style="font-size: 13px;">
                                            <a href="javascript:void(0)" onclick="copyShareLink()" style="color: #579dff; text-decoration: underline; margin-right: 8px;">Copy link</a>
                                            @if($canManageMembers)
                                            <span style="color: #9fadbc;">�</span>
                                            <a href="javascript:void(0)" onclick="deleteShareLink()" style="color: #579dff; text-decoration: underline; margin-left: 8px;">Delete link</a>
                                            @endif
                                        </div>
                                    </div>
                                    @if($canManageMembers)
                                    <!-- <button onclick="createShareLink()" id="generateLinkBtn" style="width: 100%; padding: 10px 12px; background: #0052cc; color: white; border: none; border-radius: 4px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#0047b2'" onmouseout="this.style.background='#0052cc'"> -->
                                                <button onclick="createShareLink()" id="generateLinkBtn" 
                                                    style="width: 100%; padding: 10px 16px; background: linear-gradient(135deg, #388bff 0%, #0052cc 100%); color: white; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.2); transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; gap: 8px;" 
                                                    onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.3)';" 
                                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.2)';">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                                            Generate Link
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div id="boardMembersSection" style="display: none; padding: 16px; border-bottom: 1px solid #3c444d;">
                            <div style="font-size: 12px; font-weight: 600; color: #9fadbc; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.5px;">Board Members</div>
                            <div id="boardMembersList" style="display: flex; flex-direction: column; gap: 8px;">
                            </div>
                        </div>

                        <!-- Pending Join Requests - Only for admins -->
                        @if($canManageMembers)
                        <div id="pendingApprovalsSection" style="display: none; padding: 16px; border-bottom: 1px solid #3c444d;">
                            <div style="font-size: 12px; font-weight: 600; color: #9fadbc; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.5px;">Pending Approvals</div>
                            <div id="pendingRequestsList" style="display: flex; flex-direction: column; gap: 8px;">
                            </div>
                        </div>
                        @endif
                        
                        <!-- Add Members Section - Only for managers -->
                        @if($canManageMembers)
                        <div style="padding: 16px;">
                            <div style="font-size: 12px; font-weight: 600; color: #9fadbc; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.5px;">Add Members</div>
                            <input type="text" id="memberSearchInput" placeholder="Search members..."
                                style="width:100%; padding:10px 12px; background:#2c333a; border:1px solid #3c444d; border-radius:4px; color:#b6c2cf; font-size:14px; outline:none; box-sizing:border-box; margin-bottom: 8px;"
                                onfocus="this.style.borderColor='#579dff'" onblur="this.style.borderColor='#3c444d'"
                                oninput="clearTimeout(window._searchTimer); window._searchTimer = setTimeout(() => filterDropdownMembers(this.value), 200)">
                            <div id="shareSearchResults"></div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>            </div>
            @if($canEdit ?? false)
            <div class="board-icon-btn" onclick="toggleBoardMenu()" style="position: relative; cursor: pointer;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                </svg>
                <!-- Board Menu Dropdown -->
                <div id="boardMenuDropdown" style="display: none; position: absolute; top: 40px; right: 0; background: #22272b; border: 1px solid #3c444d; border-radius: 8px; width: 200px; box-shadow: 0 8px 16px rgba(0,0,0,0.5); z-index: 1000; padding: 8px 0;">
                    <button onclick="event.stopPropagation(); openEditBoardModal()" style="width: 100%; text-align: left; padding: 10px 16px; background: transparent; border: none; color: #b6c2cf; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: background 0.2s;" onmouseover="this.style.background='#2c333a'" onmouseout="this.style.background='transparent'">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                        Edit Board
                    </button>
                    <button onclick="event.stopPropagation(); openActivitySidebar()" style="width: 100%; text-align: left; padding: 10px 16px; background: transparent; border: none; color: #b6c2cf; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: background 0.2s;" onmouseover="this.style.background='#2c333a'" onmouseout="this.style.background='transparent'">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M13 3a9 9 0 0 0-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42A8.954 8.954 0 0 0 13 21a9 9 0 0 0 0-18zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg>
                        Activity
                    </button>
                    <button onclick="event.stopPropagation(); openArchivedSidebar(); toggleBoardMenu();" style="width: 100%; text-align: left; padding: 10px 16px; background: transparent; border: none; color: #b6c2cf; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: background 0.2s;" onmouseover="this.style.background='#2c333a'" onmouseout="this.style.background='transparent'">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z"/></svg>
                        Archived Cards
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Edit Board Modal -->
    <div class="modal-overlay" id="editBoardModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
        <div class="modal-content" style="max-width: 600px; width: 90%; background: #22272b; border-radius: 8px; box-shadow: 0 8px 16px rgba(0,0,0,0.5); position: relative;">
            <div class="modal-header" style="padding: 16px 20px; border-bottom: 1px solid #3c444d; display: flex; align-items: center; justify-content: space-between;">
                <h3 style="margin: 0; font-size: 18px; color: #b6c2cf; font-weight: 600;">Edit Board</h3>
                <button class="modal-close" onclick="closeEditBoardModal()" style="background: transparent; border: none; color: #9fadbc; font-size: 24px; cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 4px; transition: all 0.2s;" onmouseover="this.style.background='#2c333a'" onmouseout="this.style.background='transparent'">�</button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <!-- Board Name -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 14px; font-weight: 600; color: #b6c2cf; margin-bottom: 8px;">Board Name</label>
                    <input type="text" id="editBoardName" value="{{ $board->name }}" style="width: 100%; padding: 10px 12px; background: #2c333a; border: 1px solid #3c444d; border-radius: 4px; color: #b6c2cf; font-size: 14px; outline: none; transition: all 0.2s;" onfocus="this.style.borderColor='#0079bf'" onblur="this.style.borderColor='#3c444d'">
                </div>
                
                <!-- Background Type -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 14px; font-weight: 600; color: #b6c2cf; margin-bottom: 8px;">Background</label>
                    <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                        <button onclick="selectBackgroundType('color')" id="colorTypeBtn" style="flex: 1; padding: 8px; background: #2c333a; border: 1px solid #3c444d; border-radius: 4px; color: #b6c2cf; font-size: 13px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#38414a'" onmouseout="this.style.background='#2c333a'">Colors</button>
                        <button onclick="selectBackgroundType('image')" id="imageTypeBtn" style="flex: 1; padding: 8px; background: #2c333a; border: 1px solid #3c444d; border-radius: 4px; color: #b6c2cf; font-size: 13px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#38414a'" onmouseout="this.style.background='#2c333a'">Photos</button>
                    </div>
                    
                    <!-- Color Options -->
                    <div id="colorOptions" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
                        <div onclick="selectBoardColor('#0079bf')" style="width: 100%; height: 80px; background: linear-gradient(135deg, #0079bf 0%, #005a8f 100%); border-radius: 8px; cursor: pointer; border: 3px solid transparent; transition: all 0.2s; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 12px; opacity: 0.7;"></div>
                        <div onclick="selectBoardColor('#d29034')" style="width: 100%; height: 80px; background: linear-gradient(135deg, #d29034 0%, #a87228 100%); border-radius: 8px; cursor: pointer; border: 3px solid transparent; transition: all 0.2s; opacity: 0.7;"></div>
                        <div onclick="selectBoardColor('#519839')" style="width: 100%; height: 80px; background: linear-gradient(135deg, #519839 0%, #3d7329 100%); border-radius: 8px; cursor: pointer; border: 3px solid transparent; transition: all 0.2s; opacity: 0.7;"></div>
                        <div onclick="selectBoardColor('#b04632')" style="width: 100%; height: 80px; background: linear-gradient(135deg, #b04632 0%, #8a3626 100%); border-radius: 8px; cursor: pointer; border: 3px solid transparent; transition: all 0.2s; opacity: 0.7;"></div>
                        <div onclick="selectBoardColor('#89609e')" style="width: 100%; height: 80px; background: linear-gradient(135deg, #89609e 0%, #6b4a7d 100%); border-radius: 8px; cursor: pointer; border: 3px solid transparent; transition: all 0.2s; opacity: 0.7;"></div>
                        <div onclick="selectBoardColor('#cd5a91')" style="width: 100%; height: 80px; background: linear-gradient(135deg, #cd5a91 0%, #a04671 100%); border-radius: 8px; cursor: pointer; border: 3px solid transparent; transition: all 0.2s; opacity: 0.7;"></div>
                        <div onclick="selectBoardColor('#4bbf6b')" style="width: 100%; height: 80px; background: linear-gradient(135deg, #4bbf6b 0%, #3a9654 100%); border-radius: 8px; cursor: pointer; border: 3px solid transparent; transition: all 0.2s; opacity: 0.7;"></div>
                        <div onclick="selectBoardColor('#00aecc')" style="width: 100%; height: 80px; background: linear-gradient(135deg, #00aecc 0%, #0087a0 100%); border-radius: 8px; cursor: pointer; border: 3px solid transparent; transition: all 0.2s; opacity: 0.7;"></div>
                    </div>
                    
                    <!-- Image Options -->
                    <div id="imageOptions" style="display: none;">
                        <!-- Popular Images Grid -->
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-bottom: 12px;">
                            <div onclick="selectBoardImage('https://images.unsplash.com/photo-1519681393784-d120267933ba?w=800&h=400&fit=crop')" style="width: 100%; height: 100px; background-image: url('https://images.unsplash.com/photo-1519681393784-d120267933ba?w=400&h=200&fit=crop'); background-size: cover; background-position: center cursor: pointer; transition: all 0.2s; position: relative; overflow: hidden;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.7)); padding: 8px; color: white; font-size: 11px; font-weight: 600;">Mountain Sky</div>
                            </div>
                            <div onclick="selectBoardImage('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=400&fit=crop')" style="width: 100%; height: 100px; background-image: url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=200&fit=crop'); background-size: cover; background-position: center;  cursor: pointer; border: 3px solid transparent; transition: all 0.2s; position: relative; overflow: hidden;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.7)); padding: 8px;  font-size: 11px; font-weight: 600;">Mountain Peak</div>
                            </div>
                            <div onclick="selectBoardImage('https://images.unsplash.com/photo-1511884642898-4c92249e20b6?w=800&h=400&fit=crop')" style="width: 100%; height: 100px; background-image: url('https://images.unsplash.com/photo-1511884642898-4c92249e20b6?w=400&h=200&fit=crop'); background-size: cover; background-position: center; cursor: pointer;  transition: all 0.2s; position: relative; overflow: hidden;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.7)); padding: 8px;  font-size: 11px; font-weight: 600;">Ocean Waves</div>
                            </div>
                            <div onclick="selectBoardImage('https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=800&h=400&fit=crop')" style="width: 100%; height: 100px; background-image: url('https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=400&h=200&fit=crop'); background-size: cover; background-position: center;  cursor: pointer;  transition: all 0.2s; position: relative; overflow: hidden;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.7)); padding: 8px;  font-size: 11px; font-weight: 600;">Forest Path</div>
                            </div>
                            <div onclick="selectBoardImage('https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800&h=400&fit=crop')" style="width: 100%; height: 100px; background-image: url('https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=400&h=200&fit=crop'); background-size: cover; background-position: center;cursor: pointer;  transition: all 0.2s; position: relative; overflow: hidden;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.7)); padding: 8px;  font-size: 11px; font-weight: 600;">Snow Mountain</div>
                            </div>
                            <div onclick="selectBoardImage('https://images.unsplash.com/photo-1475924156734-496f6cac6ec1?w=800&h=400&fit=crop')" style="width: 100%; height: 100px; background-image: url('https://images.unsplash.com/photo-1475924156734-496f6cac6ec1?w=400&h=200&fit=crop'); background-size: cover; background-position: center;  cursor: pointer; transition: all 0.2s; position: relative; overflow: hidden;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.7)); padding: 8px;font-size: 11px; font-weight: 600;">Sunset Beach</div>
                            </div>
                        </div>
                        
                        <!-- Upload Image Button (Temporarily Disabled - Use Unsplash Images) -->
                        <div style="margin-top: 12px; padding: 12px; background: #2c333a; border: 1px solid #3c444d; border-radius: 4px; text-align: center;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="opacity: 0.5; margin-bottom: 4px;">
                                <path d="M19 7v2.99s-1.99.01-2 0V7h-3s.01-1.99 0-2h3V2h2v3h3v2h-3zm-3 4V8h-3V5H5c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2v-8h-3zM5 19l3-4 2 3 3-4 4 5H5z"/>
                            </svg>
                            <div style=" font-size: 12px;">Select from beautiful images above</div>
                        </div> 
                    </div>
                </div>
                
                <!-- Save Button -->
                <button onclick="saveBoardEditChanges()" style="width: 100%; padding: 12px; background: #0079bf; color: white; border: none; border-radius: 4px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#026aa7'" onmouseout="this.style.background='#0079bf'">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- Share Modal -->
    <div class="modal-overlay" id="shareModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: flex-start; justify-content: center; padding-top: 80px;">
        <div class="modal-content" style="max-width: 500px; width: 90%; background: #ffffff; border-radius: 8px; box-shadow: 0 8px 16px rgba(0,0,0,0.3); position: relative;">
            <div class="modal-header" style="padding: 12px 16px; border-bottom: 1px solid #dfe1e6; display: flex; align-items: center; justify-content: space-between;">
                <h3 style="margin: 0; font-size: 16px; color: #172b4d; font-weight: 600;">Share board</h3>
                <button class="modal-close" onclick="closeShareModal()" style="background: transparent; border: none; color: #42526e; font-size: 24px; cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 4px; transition: all 0.2s;" onmouseover="this.style.background='#091e4221'" onmouseout="this.style.background='transparent'">�</button>
            </div>
            <div class="modal-body" style="padding: 16px;">
                <!-- Email Input with Share Button -->
                <div style="margin-bottom: 16px; display: flex; gap: 8px;">
                    <input type="text" id="shareEmailInput" placeholder="Email address or name" style="flex: 1; padding: 8px 12px; background: #fafbfc; border: 2px solid #dfe1e6; border-radius: 3px; color: #172b4d; font-size: 14px; outline: none; transition: all 0.2s;" onfocus="this.style.borderColor='#0079bf'; this.style.background='#ffffff'" onblur="this.style.borderColor='#dfe1e6'; this.style.background='#fafbfc'" oninput="filterActiveUsers(this.value)">
                    <button onclick="shareByEmail()" style="padding: 8px 16px; background: #0079bf; color: white; border: none; border-radius: 3px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.2s; white-space: nowrap;" onmouseover="this.style.background='#026aa7'" onmouseout="this.style.background='#0079bf'">Share</button>
                </div>
                
                <!-- Active Users List -->
                <div id="activeUsersSection">
                    <div style="font-size: 12px; color: #5e6c84; margin-bottom: 8px; font-weight: 600; letter-spacing: 0.5px;">WORKSPACE MEMBERS</div>
                    <div id="activeUsersList" style="max-height: 300px; overflow-y: auto; overflow-x: hidden;">
                        <!-- Active users will be loaded here -->
                    </div>
                </div>
                <!-- </div> -->
            </div>
        </div>
    </div>

    <!-- Board Content -->
    <div class="board-content {{ $board->background_type === 'image' ? '' : 'board-content-gradient' }}" 
         @if($board->background_type === 'image' && $board->background_value) 
         style="background-image: url('{{ $board->background_value }}');" 
         @elseif($board->background_type === 'color' && $board->background_value)
         style="background: linear-gradient(135deg, {{ $board->background_value }} 0%, {{ $board->background_value }}dd 100%);"
         @endif>
        <div class="lists-container">
            @foreach($board->lists as $list)
                <div class="list" data-list-id="{{ $list->id }}">
                    <div class="list-header">
                        <div class="list-title">{{ $list->name }}</div>
                        <div class="list-menu" onclick="event.stopPropagation(); toggleListMenu(event, this)">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                            </svg>
                            <div class="list-options-dropdown" style="display: none;">
                                <button type="button" class="dropdown-item" data-action="edit">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                    Edit list
                                </button>
                                <button type="button" class="dropdown-item danger" data-action="delete">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                    Delete list
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="list-cards" 
                         id="list-cards-{{ $list->id }}"
                         data-list-id="{{ $list->id }}">
                        @php
                            $labelColors = [
                                'green' => '#61bd4f',
                                'yellow' => '#f2d600',
                                'orange' => '#ff9f1a',
                                'red' => '#eb5a46',
                                'purple' => '#c377e0',
                                'blue' => '#0079bf',
                            ];
                        @endphp
                        @foreach($list->cards as $card)
                            @php
                                $cover = $card->cover ?? '';
                                $hasCover = !empty($cover);
                                
                                // Process cover style
                                $coverStyle = '';
                                if ($hasCover) {
                                    $isColor = preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $cover);
                                    if ($isColor) {
                                        $coverStyle = 'background: ' . e($cover) . ';';
                                    } else {
                                        $imageUrl = $cover;
                                        if (!str_starts_with($cover, 'http') && !str_starts_with($cover, 'data:')) {
                                            $cleanPath = str_replace(['/storage/', 'storage/'], '', $cover);
                                            $imageUrl = Storage::url($cleanPath);
                                        }
                                        $coverStyle = 'background-image: url(' . e($imageUrl) . '); background-size: cover; background-position: center;';
                                    }
                                }
                                
                                $cardLabels = $card->labels ?? [];
                                $cardLabels = is_array($cardLabels) ? $cardLabels : [];
                            @endphp
                            <div class="card {{ $hasCover ? 'has-cover' : '' }}" 
                                 data-card-id="{{ $card->id }}"
                                 data-list-id="{{ $list->id }}"
                                 data-card-title="{{ $card->title }}"
                                 data-card-desc="{{ e($card->description ?? '') }}"
                                 data-card-cover="{{ e($cover) }}"
                                 data-card-labels="{{ json_encode($cardLabels) }}"
                                 onclick="if(!window._isDragging){ const el=this; window.location.href=BASE_URL+'/boards/{{ $board->id }}/lists/'+el.getAttribute('data-list-id')+'/cards/'+el.getAttribute('data-card-id'); }">
                                @if($hasCover)
                                    @php
                                        $isImageUrl = str_starts_with($cover, 'http') || str_starts_with($cover, 'data:') || str_starts_with($cover, '/storage/');
                                        $isColor = preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $cover);
                                    @endphp
                                    @if($isColor)
                                        <div class="card-cover" style="{{ $coverStyle }}"></div>
                                    @else
                                        <div class="card-cover" id="cover-{{ $card->id }}" style="{{ $coverStyle }}">
                                            @if(!$isColor)
                                                <img src="{{ (str_starts_with($cover, 'http') || str_starts_with($cover, 'data:')) ? $cover : Storage::url(str_replace(['/storage/', 'storage/'], '', $cover)) }}"
                                                     style="width:100%;height:100%;object-fit:cover;border-radius:6px 6px 0 0;"
                                                     onerror="this.closest('.card-cover').style.display='none'; this.closest('.card').classList.remove('has-cover');"
                                                     alt="">
                                            @endif
                                        </div>
                                    @endif
                                @endif
                                @if(count($cardLabels) > 0)
                                    <div class="card-labels">
                                        @foreach($cardLabels as $lc)
                                            @php
                                                $labelColor = null;
                                                $labelName = '';
                                                
                                                // If it's an integer ID, find the label in board labels
                                                if (is_numeric($lc)) {
                                                    $foundLabel = $board->labels->firstWhere('id', $lc);
                                                    if ($foundLabel) {
                                                        $labelColor = $foundLabel->color;
                                                        $labelName = $foundLabel->name;
                                                    }
                                                }
                                                // If it's a string color name
                                                elseif (is_string($lc) && isset($labelColors[$lc])) {
                                                    $labelColor = $labelColors[$lc];
                                                    $labelName = ucfirst($lc);
                                                }
                                                // If it's an array with color
                                                elseif (is_array($lc) && !empty($lc['color'])) {
                                                    $labelColor = $lc['color'];
                                                    $labelName = $lc['name'] ?? '';
                                                }
                                            @endphp
                                            @if($labelColor)
                                                <span class="card-label" style="background:{{ $labelColor }};" title="{{ $labelName }}"></span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                                <div class="card-content">
                                    <div class="card-title">{{ $card->title }}</div>
                                    @if($canEdit ?? false)
                                    <div class="card-menu-btn" onclick="event.stopPropagation(); toggleCardMenu(event, this.closest('.card'))">
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                                        </svg>
                                    </div>
                                    @endif
                                </div>

                                <div class="card-footer">
                                    <div class="card-badges">
                                        @if($card->due_date)
                                            @php
                                                $isOverdue = $card->due_date->isPast();
                                                $isUpcoming = $card->due_date->diffInDays(now()) <= 1;
                                            @endphp
                                            <div class="card-badge due-date has-date {{ $isOverdue ? 'overdue' : '' }}">
                                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                                                {{ $card->due_date->format('M d') }}
                                            </div>
                                        @endif

                                        @if($card->description)
                                            <div class="card-badge" title="This card has a description.">
                                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 17H4v2h10v-2zm6-8H4v2h16V9zM4 15h16v-2H4v2zM4 5v2h16V5H4z"/></svg>
                                            </div>
                                        @endif

                                        @if($card->comments->count() > 0)
                                            <div class="card-badge" title="Comments">
                                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M21.99 4c0-1.1-.89-2-1.99-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4-.01-18zM18 14H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/></svg>
                                                {{ $card->comments->count() }}
                                            </div>
                                        @endif

                                        @if($card->attachments->count() > 0)
                                            <div class="card-badge" title="Attachments">
                                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.5 6v11.5c0 2.21-1.79 4-4 4s-4-1.79-4-4V5c0-1.38 1.12-2.5 2.5-2.5s2.5 1.12 2.5 2.5v10.5c0 .55-.45 1-1 1s-1-.45-1-1V6H10v9.5c0 1.38 1.12 2.5 2.5 2.5s2.5-1.12 2.5-2.5V5c0-2.21-1.79-4-4-4S7 2.79 7 5v12.5c0 3.04 2.46 5.5 5.5 5.5s5.5-2.46 5.5-5.5V6h-1.5z"/></svg>
                                                {{ $card->attachments->count() }}
                                            </div>
                                        @endif

                                        @if($card->checklistItems->count() > 0)
                                            @php
                                                $completed = $card->checklistItems->where('is_completed', true)->count();
                                                $total = $card->checklistItems->count();
                                                $allDone = $completed === $total;
                                            @endphp
                                            <div class="card-badge {{ $allDone ? 'due-date completed' : '' }}" title="Checklist items">
                                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.11 0-2 .89-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9 14l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                                                {{ $completed }}/{{ $total }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="card-members">
                                        @foreach($card->members as $member)
                                            <div class="card-member-avatar" title="{{ $member->name }}">
                                                {{ strtoupper(substr($member->name, 0, 2)) }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="add-card" onclick="showAddCardModal({{ $list->id }}, '{{ $list->name }}')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                        </svg>
                        Add a card
                    </div>
                </div>
            @endforeach
            <!-- Add List Button/Form -->
            <div id="addListContainer">
                <div class="add-list" id="addListButton" onclick="showAddListForm()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                    </svg>
                    Add another list
                </div>
                <div class="add-list-form" id="addListForm" style="display: none;">
                    <form method="POST" action="{{ route('lists.store', $board) }}" id="listForm">
                        @csrf
                        <input 
                            type="text" 
                            name="name" 
                            id="listNameInput"
                            placeholder="Enter list name..." 
                            required
                            autofocus
                        >
                        <div class="add-list-form-buttons">
                            <button type="submit" class="btn-add-list">Add list</button>
                            <button type="button" class="btn-cancel-list" onclick="hideAddListForm()">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- List Options Overlay -->
    <div id="listOptionsOverlay" class="list-options-overlay" style="display: none;" onclick="closeListMenu()"></div>

    <!-- Edit List Modal -->
    <div class="card-modal-overlay" id="editListModal" style="display: none;" onclick="closeEditListModal()">
        <div class="card-modal" onclick="event.stopPropagation()" style="max-width: 400px;">
            <div class="card-modal-header">
                <div class="card-modal-title">Edit list</div>
                <button type="button" class="card-modal-close" onclick="closeEditListModal()">�</button>
            </div>
            <div class="card-modal-body">
                <form method="POST" action="" id="editListForm">
                    @csrf
                    @method('PUT')
                    <div class="card-form-group">
                        <input 
                            type="text" 
                            name="name" 
                            id="editListNameInput"
                            placeholder="Enter list name..." 
                            required
                            autofocus
                        >
                    </div>
                    <div class="add-list-form-buttons" style="margin-top: 12px;">
                        <button type="submit" class="btn-add-list">Save</button>
                        <button type="button" class="btn-cancel-list" onclick="closeEditListModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add/Edit Card Modal -->
    <div class="card-modal-overlay" id="addCardModal">
        <div class="card-modal">
            <div class="card-modal-header">
                <div class="card-modal-title" id="cardModalListName">To Do</div>
                <button type="button" class="card-modal-close" onclick="closeAddCardModal()">�</button>
            </div>
            <div class="card-modal-body">
                <form method="POST" action="" id="cardForm" onsubmit="event.preventDefault(); submitCardForm();">
                    @csrf
                    <div id="editMethodField"></div>
                    <div class="card-form-group">
                        <input 
                            type="text" 
                            name="title" 
                            id="cardTitleInput"
                            placeholder="Enter a title or paste a link" 
                            required
                            autofocus
                        >
                    </div>
                
                </form>
            </div>
            <div class="card-modal-footer">
                <button type="button" class="btn-add-card-modal" id="cardSubmitButton" onclick="submitCardForm()">Add card</button>
                <!-- <button type="button" class="btn-tip"> -->

                </button>
            </div>
        </div>
    </div>

    <!-- Card Options Dropdown -->
    <div id="cardOptionsOverlay" class="card-options-overlay" style="display: none;" onclick="closeCardMenu()"></div>
    <div id="cardOptionsDropdown" class="card-options-dropdown" style="display: none;">
        <button type="button" class="dropdown-item" data-action="open">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 18c1.1 0 1.99-.9 1.99-2L22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2H0v2h24v-2h-4zM4 6h16v10H4V6z"/></svg>
            Open card
        </button>
        <button type="button" class="dropdown-item" data-action="edit-labels">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.63 5.84C17.27 5.33 16.67 5 16 5L5 5.01c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h11c.67 0 1.27-.33 1.63-.84L22 12l-4.37-6.16z"/></svg>
            Edit labels
        </button>
        <button type="button" class="dropdown-item" data-action="change-cover">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
            Change cover
        </button>
        <button type="button" class="dropdown-item" data-action="archive">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z"/></svg>
            Archive
        </button>
        <button type="button" class="dropdown-item danger" data-action="delete">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
            Delete
        </button>
    </div>

    <!-- Edit Labels Modal -->
    <div class="card-modal-overlay" id="labelsModal">
        <div class="card-modal" onclick="event.stopPropagation()" style="max-width: 400px;">
            <div class="card-modal-header">
                <div class="card-modal-title">Edit labels</div>
                <button type="button" class="card-modal-close" onclick="closeLabelsModal()">�</button>
            </div>
            <div class="card-modal-body labels-modal">
                <div class="label-options">
                    <span class="label-chip" data-color="green" style="background:#61bd4f;color:#fff;">Green</span>
                    <span class="label-chip" data-color="yellow" style="background:#f2d600;color:#333;">Yellow</span>
                    <span class="label-chip" data-color="orange" style="background:#ff9f1a;color:#fff;">Orange</span>
                    <span class="label-chip" data-color="red" style="background:#eb5a46;color:#fff;">Red</span>
                    <span class="label-chip" data-color="purple" style="background:#c377e0;color:#fff;">Purple</span>
                    <span class="label-chip" data-color="blue" style="background:#0079bf;color:#fff;">Blue</span>
                </div>
                <p style="margin-top:12px;font-size:13px;color:#9fadbc;">Click to toggle. Selected labels shown on card.</p>
                <button type="button" class="btn-add-card-modal" style="margin-top:16px;" onclick="saveLabels()">Save</button>
            </div>
        </div>
    </div>

    <!-- Change Cover Modal -->
    <div class="card-modal-overlay" id="coverModal">
        <div class="card-modal" onclick="event.stopPropagation()" style="max-width: 500px; max-height: 90vh; overflow-y: auto;">
            <div class="card-modal-header">
                <div class="card-modal-title">Cover</div>
                <button type="button" class="card-modal-close" onclick="closeCoverModal()">�</button>
            </div>
            <div class="card-modal-body">
                <!-- Size Section -->
                <div class="cover-section">
                    <div class="cover-section-title">Size</div>
                    <div class="cover-size-options">
                        <div class="cover-size-option selected" data-size="full" onclick="selectCoverSize('full', this)">
                            <div class="cover-size-preview full" id="previewFull"></div>
                            <div class="cover-size-label">Full</div>
                        </div>
                        <div class="cover-size-option" data-size="thumbnail" onclick="selectCoverSize('thumbnail', this)">
                            <div class="cover-size-preview thumbnail">
                                <div class="preview-content">
                                    <div class="preview-line"></div>
                                    <div class="preview-line" style="width: 60%;"></div>
                                </div>
                                <div class="preview-image" id="previewThumb"></div>
                            </div>
                            <div class="cover-size-label">Thumbnail</div>
                        </div>
                    </div>
                </div>

         
                <button type="button" class="btn-add-card-modal" onclick="removeCover()">Remove cover</button>

                <!-- Colors Section -->
                <div class="cover-section">
                    <div class="cover-section-title">Colors</div>
                    <div class="cover-colors-grid">
                        <div class="cover-color-option" data-color="#61bd4f" style="background:#61bd4f;" onclick="selectCoverColor('#61bd4f', this)"></div>
                        <div class="cover-color-option" data-color="#f2d600" style="background:#f2d600;" onclick="selectCoverColor('#f2d600', this)"></div>
                        <div class="cover-color-option" data-color="#ff9f1a" style="background:#ff9f1a;" onclick="selectCoverColor('#ff9f1a', this)"></div>
                        <div class="cover-color-option" data-color="#eb5a46" style="background:#eb5a46;" onclick="selectCoverColor('#eb5a46', this)"></div>
                        <div class="cover-color-option" data-color="#c377e0" style="background:#c377e0;" onclick="selectCoverColor('#c377e0', this)"></div>
                        <div class="cover-color-option" data-color="#0079bf" style="background:#0079bf;" onclick="selectCoverColor('#0079bf', this)"></div>
                        <div class="cover-color-option" data-color="#00c2e0" style="background:#00c2e0;" onclick="selectCoverColor('#00c2e0', this)"></div>
                        <div class="cover-color-option" data-color="#51e898" style="background:#51e898;" onclick="selectCoverColor('#51e898', this)"></div>
                        <div class="cover-color-option" data-color="#ff78cb" style="background:#ff78cb;" onclick="selectCoverColor('#ff78cb', this)"></div>
                        <div class="cover-color-option" data-color="#b3b3b3" style="background:#b3b3b3;" onclick="selectCoverColor('#b3b3b3', this)"></div>
                    </div>
                    <button type="button" class="cover-upload-btn" style="margin-top: 8px;">Enable colorblind friendly mode</button>
                </div>

                <!-- Attachments Section -->
                <div class="cover-section">
                    <div class="cover-section-title">Attachments</div>
                    <button type="button" class="cover-upload-btn" onclick="document.getElementById('coverFileInput').click()">Upload a cover image</button>
                    <input type="file" id="coverFileInput" accept="image/*" style="display:none;" onchange="handleCoverUpload(event)">
                    <div class="cover-tip">Tip: Drag an image on to the card to upload it.</div>
                </div>

                <!-- Photos from Unsplash Section -->
                <div class="cover-section">
                    <div class="cover-section-title">Photos from Unsplash</div>
                    <div class="cover-unsplash-grid" id="unsplashGrid">
                        <div class="cover-unsplash-item" onclick="selectUnsplashImage('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400', this)">
                            <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=200" alt="Nature">
                        </div>
                        <div class="cover-unsplash-item" onclick="selectUnsplashImage('https://images.unsplash.com/photo-1519681393784-d120267933ba?w=400', this)">
                            <img src="https://images.unsplash.com/photo-1519681393784-d120267933ba?w=200" alt="Mountain">
                        </div>
                        <div class="cover-unsplash-item" onclick="selectUnsplashImage('https://images.unsplash.com/photo-1501594907352-04cda38ebc29?w=400', this)">
                            <img src="https://images.unsplash.com/photo-1501594907352-04cda38ebc29?w=200" alt="Sunset">
                        </div>
                        <div class="cover-unsplash-item" onclick="selectUnsplashImage('https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=400', this)">
                            <img src="https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=200" alt="Landscape">
                        </div>
                        <div class="cover-unsplash-item" onclick="selectUnsplashImage('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400', this)">
                            <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=200" alt="Nature">
                        </div>
                        <div class="cover-unsplash-item" onclick="selectUnsplashImage('https://images.unsplash.com/photo-1514565131-fce0801e5785?w=400', this)">
                            <img src="https://images.unsplash.com/photo-1514565131-fce0801e5785?w=200" alt="Architecture">
                        </div>
                    </div>
                    <button type="button" class="cover-search-btn" onclick="searchUnsplash()">Search for photos</button>
                </div>

                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #016bd5ff;">
                    <button type="button" class="btn-add-card-modal" onclick="saveCover()">Save</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentListId = null;
        const boardId = {{ $board->id }};
        let activeCardEl = null;
        
        // Initialize SortableJS for each list
        function initSortable() {
            if (typeof Sortable === 'undefined') {
                setTimeout(initSortable, 100);
                return;
            }
            
            const lists = document.querySelectorAll('.list-cards');
            
            if (lists.length === 0) {
                return;
            }
            
            lists.forEach(function(listContainer) {
                const listId = parseInt(listContainer.getAttribute('data-list-id'));
                
                new Sortable(listContainer, {
                    group: 'kanban-cards',
                    animation: 200,
                    handle: '.card',
                    dragClass: 'card-dragging',
                    ghostClass: 'card-ghost',
                    chosenClass: 'card-chosen',
                    forceFallback: false,
                    fallbackOnBody: true,
                    swapThreshold: 0.65,
                    filter: '.add-card',
                    preventOnFilter: false,
                    onStart: function(evt) {
                        window._isDragging = true;
                    },
                    onEnd: function(evt) {
                        setTimeout(() => { window._isDragging = false; }, 100);
                        const cardEl = evt.item;
                        const cardId = parseInt(cardEl.getAttribute('data-card-id'));
                        const sourceListId = parseInt(evt.from.getAttribute('data-list-id'));
                        const targetListId = parseInt(evt.to.getAttribute('data-list-id'));
                        
                        // Update data-list-id immediately on the card element
                        cardEl.setAttribute('data-list-id', targetListId);
                        
                        const newPosition = evt.newIndex;
                        moveCard(cardId, sourceListId, targetListId, newPosition);
                    }
                });
            });
        }
        
        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initSortable);
        } else {
            initSortable();
        }

        // Initialize SortableJS for lists container
        function initListsSortable() {
            if (typeof Sortable === 'undefined') {
                setTimeout(initListsSortable, 100);
                return;
            }
            
            const listsContainer = document.querySelector('.lists-container');
            if (!listsContainer) {
                return;
            }
            
            new Sortable(listsContainer, {
                animation: 200,
                handle: '.list-title',
                dragClass: 'list-dragging',
                ghostClass: 'list-ghost',
                chosenClass: 'list-chosen',
                forceFallback: false,
                fallbackOnBody: true,
                swapThreshold: 0.65,
                filter: '#addListContainer, .add-list, .add-list-form, .list-menu',
                preventOnFilter: false,
                onStart: function(evt) {
                },
                onEnd: function(evt) {
                    const listId = parseInt(evt.item.getAttribute('data-list-id'));
                    const newIndex = evt.newIndex;
                    
                    // Get all list IDs in new order
                    const listIds = Array.from(listsContainer.querySelectorAll('.list[data-list-id]'))
                        .map(listEl => parseInt(listEl.getAttribute('data-list-id')));
                    
                    // Reorder lists via AJAX
                    reorderLists(listIds);
                }
            });
        }

        // Initialize lists sortable when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initListsSortable);
        } else {
            initListsSortable();
        }

        function reorderLists(listIds) {
            const url = `${BASE_URL}/boards/${boardId}/lists/reorder`;
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    list_ids: listIds
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    showToast(data.error || 'Error reordering lists', 'error');
                } else {
                    showToast('Lists reordered successfully', 'success');
                }
            })
            .catch(error => {
                showToast('Error reordering lists', 'error');
            });
        }

        function moveCard(cardId, sourceListId, targetListId, position) {
            const url = `${BASE_URL}/boards/${boardId}/lists/${sourceListId}/cards/${cardId}/move`;
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    list_id: targetListId,
                    position: position
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    showToast(data.error || 'Error moving card', 'error');
                } else {
                    // Update data-list-id on card element so click URL is correct
                    const cardEl = document.querySelector(`.card[data-card-id="${cardId}"]`);
                    if (cardEl) cardEl.setAttribute('data-list-id', targetListId);
                }
            })
            .catch(error => {
                showToast('Error moving card', 'error');
            });
        }

        let activeListEl = null;

        function toggleListMenu(ev, menuBtn) {
            ev.stopPropagation();
            const dropdown = menuBtn.querySelector('.list-options-dropdown');
            const overlay = document.getElementById('listOptionsOverlay');
            
            // Close all other menus first
            document.querySelectorAll('.list-options-dropdown').forEach(dd => {
                if (dd !== dropdown) {
                    dd.style.display = 'none';
                }
            });
            
            if (dropdown.style.display === 'block') {
                closeListMenu();
                return;
            }
            
            activeListEl = menuBtn.closest('.list');
            overlay.style.display = 'block';
            dropdown.style.display = 'block';
            
            // Position dropdown
            const rect = menuBtn.getBoundingClientRect();
            dropdown.style.position = 'fixed';
            dropdown.style.top = (rect.bottom + 4) + 'px';
            dropdown.style.right = (window.innerWidth - rect.right) + 'px';
            dropdown.style.left = 'auto';
            
            // Handle dropdown item clicks
            dropdown.querySelectorAll('.dropdown-item').forEach(btn => {
                btn.onclick = function(e) {
                    e.stopPropagation();
                    const action = this.getAttribute('data-action');
                    if (action === 'edit') {
                        editList();
                    } else if (action === 'delete') {
                        deleteList();
                    }
                };
            });
        }

        function closeListMenu() {
            document.querySelectorAll('.list-options-dropdown').forEach(dd => {
                dd.style.display = 'none';
            });
            document.getElementById('listOptionsOverlay').style.display = 'none';
            activeListEl = null;
        }

        function editList() {
            if (!activeListEl) return;
            const listId = activeListEl.getAttribute('data-list-id');
            const listTitle = activeListEl.querySelector('.list-title').textContent;
            
            document.getElementById('editListNameInput').value = listTitle;
            document.getElementById('editListForm').action = `${BASE_URL}/boards/${boardId}/lists/${listId}`;
            document.getElementById('editListModal').style.display = 'flex';
            closeListMenu();
        }

        function closeEditListModal() {
            document.getElementById('editListModal').style.display = 'none';
            document.getElementById('editListNameInput').value = '';
        }

        function deleteList() {
            if (!activeListEl) return;
            const listId = activeListEl.getAttribute('data-list-id');
            const listTitle = activeListEl.querySelector('.list-title').textContent;
            
            Swal.fire({
                title: 'Delete list?',
                text: `Are you sure you want to delete "${listTitle}"? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                background: '#22272b',
                color: '#b6c2cf',
                customClass: {
                    popup: 'swal2-dark',
                    confirmButton: 'swal2-confirm-dark',
                    cancelButton: 'swal2-cancel-dark'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `${BASE_URL}/boards/${boardId}/lists/${listId}`;
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';
                    
                    const csrfField = document.createElement('input');
                    csrfField.type = 'hidden';
                    csrfField.name = '_token';
                    csrfField.value = csrfToken;
                    
                    form.appendChild(methodField);
                    form.appendChild(csrfField);
                    document.body.appendChild(form);
                    form.submit();
                }
                closeListMenu();
            });
        }

        function toggleCardMenu(ev, cardEl) {
            ev.stopPropagation();
            const btn = ev.currentTarget;
            const rect = btn.getBoundingClientRect();
            const dd = document.getElementById('cardOptionsDropdown');
            const ov = document.getElementById('cardOptionsOverlay');
            if (dd.style.display === 'block') {
                closeCardMenu();
                return;
            }
            activeCardEl = cardEl;
            ov.style.display = 'block';
            dd.style.display = 'block';
            dd.style.position = 'fixed';
            dd.style.top = (rect.bottom + 4) + 'px';
            dd.style.left = Math.min(rect.left, window.innerWidth - 220) + 'px';
            dd.style.right = 'auto';
        }

        function closeCardMenu() {
            document.getElementById('cardOptionsOverlay').style.display = 'none';
            document.getElementById('cardOptionsDropdown').style.display = 'none';
            activeCardEl = null;
        }

        document.getElementById('cardOptionsDropdown').querySelectorAll('.dropdown-item').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const action = this.getAttribute('data-action');
                if (!activeCardEl) {
                    return;
                }
                
                // Store card element before closing menu
                const cardEl = activeCardEl;
                const cardId = cardEl.getAttribute('data-card-id');
                const listId = cardEl.getAttribute('data-list-id');
                const boardUrl = BASE_URL + '/boards/{{ $board->id }}';
                const cardUrl = `${boardUrl}/lists/${listId}/cards/${cardId}`;
                
                // Close menu first
                closeCardMenu();
                
                // Then handle actions
                if (action === 'open') {
                    window.location.href = cardUrl;
                } else if (action === 'edit-labels') {
                    setTimeout(() => openLabelsModal(cardEl), 100);
                } else if (action === 'change-cover') {
                    setTimeout(() => openCoverModal(cardEl), 100);
                } else if (action === 'archive') {
                    console.log('Archiving card from board view:', cardId);
                    fetch(cardUrl + '/archive', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Archive response:', data);
                        if (data.success) {
                            // Remove card from DOM
                            cardEl.remove();
                            
                            // Show success toast
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Card archived',
                                showConfirmButton: false,
                                timer: 2000,
                                background: '#22272b',
                                color: '#b6c2cf',
                                customClass: {
                                    popup: 'swal2-toast-dark'
                                }
                            });
                        } else {
                            console.error('Archive failed:', data);
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: 'Failed to archive card',
                                showConfirmButton: false,
                                timer: 2000,
                                background: '#22272b',
                                color: '#b6c2cf',
                                customClass: {
                                    popup: 'swal2-toast-dark'
                                }
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Archive error:', error);
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'Failed to archive card',
                            showConfirmButton: false,
                            timer: 2000,
                            background: '#22272b',
                            color: '#b6c2cf',
                            customClass: {
                                popup: 'swal2-toast-dark'
                            }
                        });
                    });
                } else if (action === 'delete') {
                    Swal.fire({
                        title: 'Delete card?',
                        text: 'Are you sure you want to delete this card? This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e74c3c',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Delete',
                        cancelButtonText: 'Cancel',
                        background: '#22272b',
                        color: '#b6c2cf',
                        customClass: {
                            popup: 'swal2-dark',
                            confirmButton: 'swal2-confirm-dark',
                            cancelButton: 'swal2-cancel-dark'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            console.log('Deleting card from board view:', cardId);
                            fetch(cardUrl, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log('Delete response:', data);
                                if (data.success) {
                                    // Remove card from DOM
                                    cardEl.remove();
                                    
                                    // Show success toast
                                    Swal.fire({
                                        toast: true,
                                        position: 'top-end',
                                        icon: 'success',
                                        title: 'Card deleted',
                                        showConfirmButton: false,
                                        timer: 2000,
                                        background: '#22272b',
                                        color: '#b6c2cf',
                                        customClass: {
                                            popup: 'swal2-toast-dark'
                                        }
                                    });
                                } else {
                                    console.error('Delete failed:', data);
                                    Swal.fire({
                                        toast: true,
                                        position: 'top-end',
                                        icon: 'error',
                                        title: 'Failed to delete card',
                                        showConfirmButton: false,
                                        timer: 2000,
                                        background: '#22272b',
                                        color: '#b6c2cf',
                                        customClass: {
                                            popup: 'swal2-toast-dark'
                                        }
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Delete error:', error);
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'error',
                                    title: 'Failed to delete card',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    background: '#22272b',
                                    color: '#b6c2cf',
                                    customClass: {
                                        popup: 'swal2-toast-dark'
                                    }
                                });
                            });
                        }
                    });
                }
            });
        });
    
        let selectedLabels = [];
        function openLabelsModal(cardEl) {
            activeCardEl = cardEl;
            try {
                selectedLabels = JSON.parse(cardEl.getAttribute('data-card-labels') || '[]');
            } catch (_) { selectedLabels = []; }
            document.querySelectorAll('.label-chip').forEach(chip => {
                const c = chip.getAttribute('data-color');
                chip.classList.toggle('selected', selectedLabels.includes(c));
            });
            document.getElementById('labelsModal').classList.add('active');
        }
        function closeLabelsModal() {
            document.getElementById('labelsModal').classList.remove('active');
            activeCardEl = null;
        }
        
        function updateCardLabelsDisplay() {
            if (!activeCardEl) return;
            
            // Remove existing labels from card
            const existingLabels = activeCardEl.querySelector('.card-labels');
            if (existingLabels) {
                existingLabels.innerHTML = '';
                
                // Add new labels
                selectedLabels.forEach(color => {
                    const labelSpan = document.createElement('span');
                    labelSpan.className = 'card-label';
                    labelSpan.style.backgroundColor = color;
                    existingLabels.appendChild(labelSpan);
                });
            }
        }
        
        document.querySelectorAll('.label-chip').forEach(chip => {
            chip.addEventListener('click', function() {
                const c = this.getAttribute('data-color');
                const i = selectedLabels.indexOf(c);
                if (i >= 0) selectedLabels.splice(i, 1);
                else selectedLabels.push(c);
                document.querySelectorAll('.label-chip').forEach(el => {
                    el.classList.toggle('selected', selectedLabels.includes(el.getAttribute('data-color')));
                });
            });
        });
        document.getElementById('labelsModal').addEventListener('click', function(e) {
            if (e.target === this) closeLabelsModal();
        });
        function saveLabels() {
            if (!activeCardEl) return;
            const cardId = activeCardEl.getAttribute('data-card-id');
            const listId = activeCardEl.getAttribute('data-list-id');
            fetch(`${BASE_URL}/boards/${boardId}/lists/${listId}/cards/${cardId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ labels: selectedLabels })
            }).then(r => r.json()).then((data) => { 
                if (data && data.success !== false) {
                    showToast('Labels updated successfully', 'success');
                    closeLabelsModal();
                    // Update card labels display without reload
                    updateCardLabelsDisplay();
                } else {
                    showToast(data.error || 'Error updating labels', 'error');
                }
            }).catch(() => {
                showToast('Error updating labels', 'error');
            });
        }

        let selectedCover = '';
        let coverSize = 'full';
        let coverType = 'color'; // 'color', 'image', 'gradient'

        function openCoverModal(cardEl) {
            if (!cardEl) {
                return;
            }
            
            activeCardEl = cardEl;
            selectedCover = cardEl.getAttribute('data-card-cover') || '';
            coverSize = 'full';
            
            const modal = document.getElementById('coverModal');
            if (!modal) {
                return;
            }
            
            // Update previews
            updateCoverPreviews();
            
            // Select appropriate option based on current cover
            if (!selectedCover) {
                document.querySelectorAll('.cover-color-option, .cover-unsplash-item').forEach(el => el.classList.remove('selected'));
            } else if (selectedCover.startsWith('#')) {
                document.querySelectorAll('.cover-color-option').forEach(el => {
                    el.classList.toggle('selected', el.getAttribute('data-color') === selectedCover);
                });
                document.querySelectorAll('.cover-unsplash-item').forEach(el => el.classList.remove('selected'));
            } else {
                document.querySelectorAll('.cover-unsplash-item').forEach(el => {
                    const img = el.querySelector('img');
                    if (img && img.src) {
                        const fullUrl = img.src.replace('w=200', 'w=400');
                        el.classList.toggle('selected', fullUrl === selectedCover);
                    }
                });
                document.querySelectorAll('.cover-color-option').forEach(el => el.classList.remove('selected'));
            }
            
            // Reset size selection
            document.querySelectorAll('.cover-size-option').forEach(o => o.classList.remove('selected'));
            document.querySelector('.cover-size-option[data-size="full"]')?.classList.add('selected');
            
            modal.classList.add('active');
        }

        function closeCoverModal() {
            document.getElementById('coverModal').classList.remove('active');
            activeCardEl = null;
            selectedCover = '';
        }

        function selectCoverSize(size, el) {
            coverSize = size;
            document.querySelectorAll('.cover-size-option').forEach(o => o.classList.remove('selected'));
            el.classList.add('selected');
            updateCoverPreviews();
        }

        function updateCoverPreviews() {
            const previewFull = document.getElementById('previewFull');
            const previewThumb = document.getElementById('previewThumb');
            
            if (!previewFull || !previewThumb) {
                return;
            }
            
            if (selectedCover) {
                if (selectedCover.startsWith('#')) {
                    previewFull.style.background = selectedCover;
                    previewFull.style.backgroundImage = '';
                    previewThumb.style.background = selectedCover;
                    previewThumb.style.backgroundImage = '';
                } else if (selectedCover.startsWith('http') || selectedCover.startsWith('https') || selectedCover.startsWith('data:')) {
                    previewFull.style.backgroundImage = `url(${selectedCover})`;
                    previewFull.style.background = '';
                    previewThumb.style.backgroundImage = `url(${selectedCover})`;
                    previewThumb.style.background = '';
                } else {
                    previewFull.style.background = selectedCover;
                    previewFull.style.backgroundImage = '';
                    previewThumb.style.background = selectedCover;
                    previewThumb.style.backgroundImage = '';
                }
            } else {
                previewFull.style.background = '#2c333a';
                previewFull.style.backgroundImage = '';
                previewThumb.style.background = '#2c333a';
                previewThumb.style.backgroundImage = '';
            }
        }

        function selectCoverColor(color, el) {
            selectedCover = color;
            coverType = 'color';
            document.querySelectorAll('.cover-color-option').forEach(o => o.classList.remove('selected'));
            el.classList.add('selected');
            document.querySelectorAll('.cover-unsplash-item').forEach(o => o.classList.remove('selected'));
            updateCoverPreviews();
        }

        function selectUnsplashImage(url, el) {
            selectedCover = url;
            coverType = 'image';
            document.querySelectorAll('.cover-unsplash-item').forEach(o => o.classList.remove('selected'));
            el.classList.add('selected');
            document.querySelectorAll('.cover-color-option').forEach(o => o.classList.remove('selected'));
            updateCoverPreviews();
        }

        function removeCover() {
            selectedCover = '';
            document.querySelectorAll('.cover-color-option, .cover-unsplash-item').forEach(el => el.classList.remove('selected'));
            updateCoverPreviews();
            saveCover();
        }

        function handleCoverUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            const cardId = activeCardEl.getAttribute('data-card-id');
            const listId = activeCardEl.getAttribute('data-list-id');
            
            const formData = new FormData();
            formData.append('file', file);
            
            fetch(`${BASE_URL}/boards/${boardId}/lists/${listId}/cards/${cardId}/upload-cover`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    selectedCover = data.cover;
                    coverType = 'image';
                    document.querySelectorAll('.cover-color-option, .cover-unsplash-item').forEach(el => el.classList.remove('selected'));
                    updateCoverPreviews();
                    
                    // Update the card on the board
                    activeCardEl.setAttribute('data-card-cover', data.cover);
                    activeCardEl.classList.add('has-cover');
                    let coverEl = activeCardEl.querySelector('.card-cover');
                    if (!coverEl) {
                        coverEl = document.createElement('div');
                        coverEl.className = 'card-cover';
                        activeCardEl.insertBefore(coverEl, activeCardEl.firstChild);
                    }
                    coverEl.innerHTML = `<img src="${data.cover}" alt="cover">`;
                    
                    showToast('Cover updated!', 'success');
                    if (event.target) event.target.value = '';
                }
            })
            .catch(error => showToast('Failed to upload cover', 'error'));
        }

        function searchUnsplash() {
            const query = prompt('Search for photos (e.g., nature, mountains, sunset):');
            if (!query) return;
            // In a real implementation, you'd call Unsplash API here
            showToast('Unsplash search would open here. For now, select from available images.', 'info');
        }

        function saveCover() {
            if (!activeCardEl) return;
            const cardId = activeCardEl.getAttribute('data-card-id');
            const listId = activeCardEl.getAttribute('data-list-id');
            fetch(`${BASE_URL}/boards/${boardId}/lists/${listId}/cards/${cardId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ cover: selectedCover })
            }).then(r => {
                if (!r.ok) return r.json().then(d => { throw new Error(d.error || 'Failed to save cover'); });
                return r.json();
            }).then((data) => {
                if (!data || !data.success) throw new Error('Failed to save cover');
                activeCardEl.setAttribute('data-card-cover', selectedCover);
                // Update or add card cover visually
                let coverEl = activeCardEl.querySelector('.card-cover');
                if (selectedCover) {
                    const isImage = /^https?:\/\//.test(selectedCover) || /^data:/.test(selectedCover);
                    if (!coverEl) {
                        coverEl = document.createElement('div');
                        coverEl.className = 'card-cover';
                        activeCardEl.classList.add('has-cover');
                        activeCardEl.insertBefore(coverEl, activeCardEl.querySelector('.card-content'));
                    }
                    if (isImage) {
                        coverEl.style.backgroundImage = `url("${String(selectedCover).replace(/"/g, '\\"')}")`;
                        coverEl.style.background = '';
                    } else {
                        coverEl.style.background = selectedCover;
                        coverEl.style.backgroundImage = '';
                    }
                } else {
                    if (coverEl) {
                        coverEl.remove();
                        activeCardEl.classList.remove('has-cover');
                    }
                }
                closeCoverModal();
            }).catch((err) => {
                showToast(err.message || 'Could not save cover. Please try again.', 'error');
            });
        }

        document.getElementById('coverModal').addEventListener('click', function(e) {
            if (e.target === this) closeCoverModal();
        });

        let isEditMode = false;

        function showAddCardModal(listId, listName) {
            isEditMode = false;
            currentListId = listId;
            document.getElementById('cardModalListName').textContent = listName;
            document.getElementById('cardForm').action = '{{ route("cards.store", [$board, ":list"]) }}'.replace(':list', listId);
            document.getElementById('cardTitleInput').value = '';
            const descInput = document.getElementById('cardDescriptionInput');
            if (descInput) descInput.value = '';
            document.getElementById('editMethodField').innerHTML = '';
            document.getElementById('cardSubmitButton').textContent = 'Add card';
            document.getElementById('addCardModal').classList.add('active');
            document.getElementById('cardTitleInput').focus();
        }

        function showEditCardModal(cardId, listId, cardTitle, cardDescription) {
            isEditMode = true;
            currentListId = listId;
            document.getElementById('cardModalListName').textContent = 'Edit Card';
            document.getElementById('cardForm').action = '{{ route("cards.update", [$board, ":list", ":card"]) }}'.replace(':list', listId).replace(':card', cardId);
            document.getElementById('cardTitleInput').value = cardTitle;
            const descInput = document.getElementById('cardDescriptionInput');
            if (descInput) descInput.value = cardDescription || '';
            document.getElementById('editMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('cardSubmitButton').textContent = 'Save';
            document.getElementById('addCardModal').classList.add('active');
            document.getElementById('cardTitleInput').focus();
        }

        function closeAddCardModal() {
            document.getElementById('addCardModal').classList.remove('active');
            document.getElementById('cardForm').reset();
            document.getElementById('editMethodField').innerHTML = '';
            isEditMode = false;
            currentListId = null;
        }

        function submitCardForm() {
            const titleInput = document.getElementById('cardTitleInput');
            const descInput = document.getElementById('cardDescriptionInput');
            const form = document.getElementById('cardForm');
            
            if (!titleInput.value.trim()) {
                titleInput.focus();
                return;
            }

            if (isEditMode) {
                form.submit();
                return;
            }

            const url = form.action;
            const data = {
                title: titleInput.value,
                description: descInput ? descInput.value : '',
            };

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Option 1: Live add to UI (Better UX)
                    const listCardsContainer = document.getElementById(`list-cards-${currentListId}`);
                    if (listCardsContainer) {
                        const newCard = result.card;
                        const cardHtml = `
                            <div class="card" 
                                 data-card-id="${newCard.id}"
                                 data-list-id="${currentListId}"
                                 data-card-title="${newCard.title}"
                                 data-card-desc="${newCard.description || ''}"
                                 data-card-cover=""
                                 data-card-labels="[]"
                                 onclick="window.location.href=BASE_URL+'/boards/${boardId}/lists/${currentListId}/cards/${newCard.id}'">
                                <div class="card-content">
                                    <div class="card-title">${newCard.title}</div>
                                    @if($canEdit ?? false)
                                    <div class="card-menu-btn" onclick="event.stopPropagation(); toggleCardMenu(event, this.closest('.card'))">
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                                        </svg>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        `;
                        // Insert before the "Add a card" button
                        const addCardBtn = listCardsContainer.querySelector('.add-card');
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = cardHtml.trim();
                        listCardsContainer.insertBefore(tempDiv.firstChild, addCardBtn);
                    }
                    
                    closeAddCardModal();
                    showToast('Card created successfully', 'success');
                } else {
                    showToast(result.error || 'Error creating card', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Something went wrong', 'error');
            });
        }

        // Close modal on overlay click
        document.getElementById('addCardModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddCardModal();
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddCardModal();
                closeLabelsModal();
                closeCoverModal();
                closeCardMenu();
            }
        });

        function showAddListForm() {
            document.getElementById('addListButton').style.display = 'none';
            document.getElementById('addListForm').style.display = 'block';
            document.getElementById('listNameInput').focus();
        }

        function hideAddListForm() {
            document.getElementById('addListButton').style.display = 'flex';
            document.getElementById('addListForm').style.display = 'none';
            document.getElementById('listForm').reset();
        }

        // Prevent form from closing when clicking inside
        document.getElementById('addListForm')?.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Show toast notifications for session messages
        @if(session('success'))
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#22272b',
                color: '#b6c2cf',
                customClass: {
                    popup: 'swal2-toast-dark'
                }
            });
        @endif

        @if(session('error'))
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: '{{ session('error') }}',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                background: '#22272b',
                color: '#b6c2cf',
                customClass: {
                    popup: 'swal2-toast-dark'
                }
            });
        @endif

        @if($errors->any())
            @foreach($errors->all() as $error)
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: '{{ $error }}',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    background: '#22272b',
                    color: '#b6c2cf',
                    customClass: {
                        popup: 'swal2-toast-dark'
                    }
                });
            @endforeach
        @endif

        // Helper function to show toast notifications
        function showToast(message, type = 'success') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: type === 'success' ? 3000 : 4000,
                timerProgressBar: true,
                background: '#22272b',
                color: '#b6c2cf',
                customClass: {
                    popup: 'swal2-toast-dark'
                }
            });
        }

        // Listen for card updates from card view (real-time updates)
        window.addEventListener('storage', function(e) {
            if (e.key === 'cardUpdate' && e.newValue) {
                try {
                    const update = JSON.parse(e.newValue);
                    const cardElement = document.querySelector(`[data-card-id="${update.cardId}"]`);
                    
                    if (!cardElement) return;
                    
                    if (update.type === 'cover') {
                        updateCardCoverInDOM(cardElement, update.value);
                    } else if (update.type === 'labels') {
                        updateCardLabelsInDOM(cardElement, update.value);
                    }
                    
                    // Clear the update
                    localStorage.removeItem('cardUpdate');
                } catch (err) {
                    console.error('Error processing card update:', err);
                }
            }
        });

        // Helper function to update cover in DOM
        function updateCardCoverInDOM(cardElement, coverValue) {
            let coverDiv = cardElement.querySelector('.card-cover');
            const isColor = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(coverValue);
            
            if (!coverDiv && coverValue) {
                // Create cover div if it doesn't exist
                coverDiv = document.createElement('div');
                coverDiv.className = 'card-cover';
                cardElement.insertBefore(coverDiv, cardElement.firstChild);
                cardElement.classList.add('has-cover');
            }
            
            if (coverDiv) {
                if (coverValue) {
                    if (isColor) {
                        coverDiv.style.cssText = `height: 160px; margin: -12px -12px 8px -12px; border-radius: 6px 6px 0 0; background: ${coverValue};`;
                    } else {
                        coverDiv.style.cssText = `height: 160px; margin: -12px -12px 8px -12px; border-radius: 6px 6px 0 0; background-image: url('${coverValue}'); background-size: cover; background-position: center;`;
                    }
                } else {
                    // Remove cover
                    coverDiv.remove();
                    cardElement.classList.remove('has-cover');
                }
            }
            
            // Update data attribute
            cardElement.setAttribute('data-card-cover', coverValue || '');
        }

        // Helper function to update labels in DOM
        function updateCardLabelsInDOM(cardElement, labels) {
            let labelsDiv = cardElement.querySelector('.card-labels');
            
            if (!labels || labels.length === 0) {
                if (labelsDiv) labelsDiv.remove();
                cardElement.setAttribute('data-card-labels', '[]');
                return;
            }
            
            if (!labelsDiv) {
                // Create labels div if it doesn't exist
                labelsDiv = document.createElement('div');
                labelsDiv.className = 'card-labels';
                const coverDiv = cardElement.querySelector('.card-cover');
                const cardContent = cardElement.querySelector('.card-content');
                if (coverDiv) {
                    coverDiv.insertAdjacentElement('afterend', labelsDiv);
                } else if (cardContent) {
                    cardElement.insertBefore(labelsDiv, cardContent);
                }
            }
            
            // Clear and rebuild labels
            labelsDiv.innerHTML = '';
            
            const labelColors = {
                'green': '#61bd4f', 'yellow': '#f2d600', 'orange': '#ff9f1a',
                'red': '#eb5a46', 'purple': '#c377e0', 'blue': '#0079bf'
            };
            
            labels.forEach(labelRef => {
                let color = '';
                
                // If it's an ID, find in boardLabels (from PHP)
                if (typeof labelRef === 'number' || !isNaN(labelRef)) {
                    const boardLabelsData = @json($board->labels ?? []);
                    const fullLabel = boardLabelsData.find(l => l.id == labelRef);
                    if (fullLabel) {
                        color = fullLabel.color;
                    }
                } else if (typeof labelRef === 'string') {
                    color = labelColors[labelRef] || '#61bd4f';
                } else if (labelRef && labelRef.color) {
                    color = labelRef.color;
                }
                
                if (color) {
                    const labelSpan = document.createElement('span');
                    labelSpan.className = 'card-label';
                    labelSpan.style.background = color;
                    labelsDiv.appendChild(labelSpan);
                }
            });
            
            // Update data attribute
            cardElement.setAttribute('data-card-labels', JSON.stringify(labels));
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

        // Share Dropdown - Search Based

        let allBoardSharedIds = new Set();

        let allShareableUsers = [];



        function updateBoardMemberAvatars(users) {
            const container = document.getElementById('boardMemberAvatars');
            if (!container) return;
            container.innerHTML = '';
            const visible = users.slice(0, 5);
            visible.forEach((u, i) => {
                const div = document.createElement('div');
                div.title = u.name;
                div.style.cssText = `width:32px;height:32px;border-radius:50%;background:#0052cc;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;border:2px solid rgba(0,0,0,0.3);margin-left:${i === 0 ? '0' : '-6px'};cursor:default;`;
                div.textContent = u.name.charAt(0).toUpperCase();
                container.appendChild(div);
            });
            if (users.length > 5) {
                const more = document.createElement('div');
                more.title = `${users.length - 5} more`;
                more.style.cssText = 'width:32px;height:32px;border-radius:50%;background:#3d444d;color:#9fadbc;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;border:2px solid rgba(0,0,0,0.3);margin-left:-6px;';
                more.textContent = `+${users.length - 5}`;
                container.appendChild(more);
            }   
        }
        window.toggleMembersDropdown = function toggleMembersDropdown() {

            const dropdown = document.getElementById('membersDropdown');

            if (!dropdown) {
                console.error('membersDropdown element not found');
                return;
            }

            const isVisible = dropdown.style.display === 'block';

            dropdown.style.display = isVisible ? 'none' : 'block';

            if (!isVisible) {

                loadBoardMembers();
                loadExistingShareLink();
                loadShareableUsers();
                @if($canManageMembers)
                loadPendingRequests();
                window._pendingPollInterval = setInterval(loadPendingRequests, 5000);
                @endif

                const input = document.getElementById('memberSearchInput');

                if (input) { input.value = ''; }

                const searchResults = document.getElementById('shareSearchResults');
                if (searchResults) { searchResults.innerHTML = ''; }

                setTimeout(() => {

                    const closeHandler = (e) => {

                        if (!dropdown.contains(e.target) && !e.target.closest('button[onclick="toggleMembersDropdown()"]')) {

                            dropdown.style.display = 'none';
                            clearInterval(window._pendingPollInterval);
                            document.removeEventListener('click', closeHandler);

                        }

                    };

                    document.addEventListener('click', closeHandler);

                }, 0);

            } else {
                clearInterval(window._pendingPollInterval);
            }

        }



        function loadShareableUsers() {
            const input = document.getElementById('memberSearchInput');
            if (input) { input.placeholder = 'Loading...'; input.disabled = true; }

            Promise.all([
                fetch(`${BASE_URL}/boards/{{ $board->id }}/shared-users`, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }).then(r => r.json()),
                fetch(`${BASE_URL}/boards/{{ $board->id }}/active-users`, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }).then(r => r.json())
            ]).then(([shared, active]) => {
                allBoardSharedIds = new Set((shared.users || []).map(u => u.id));
                const sharedUsers = shared.users || [];
                const activeUsers = active.users || [];
                allShareableUsers = [...sharedUsers, ...activeUsers];

                if (input) { input.placeholder = 'Search members...'; input.disabled = false; input.focus(); }

                // Update header avatars
                updateBoardMemberAvatars(sharedUsers);
            }).catch(() => {
                if (input) { input.placeholder = 'Search members...'; input.disabled = false; }
            });
        }


        function renderShareRow(u, isMember) {
            const initials = u.name.charAt(0).toUpperCase();
            const email = u.email || '';
            const action = isMember
                ? `<button onclick="event.stopPropagation(); toggleBoardAccess(${u.id}, '${u.name.replace(/'/g,"\\'")}', true)" style="background:#eb5a46;color:#fff;border:none;border-radius:3px;padding:4px 10px;font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap;">Remove</button>`
                : `<button onclick="event.stopPropagation(); toggleBoardAccess(${u.id}, '${u.name.replace(/'/g,"\\'")}', false)" style="background:#0c66e4;color:#fff;border:none;border-radius:3px;padding:4px 10px;font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap;">Add</button>`;
            const div = document.createElement('div');
            div.style.cssText = 'display:flex;align-items:center;gap:10px;padding:8px;border-radius:4px;';
            div.innerHTML = '<div style="width:28px;height:28px;border-radius:50%;background:#0052cc;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;">' + initials + '</div><div style="flex:1;min-width:0;"><div style="color:#b6c2cf;font-size:13px;font-weight:500;">' + u.name + '</div><div style="color:#9fadbc;font-size:11px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + email + '</div></div>' + action;
            return div.outerHTML;
        }


        function filterDropdownMembers(query) {
            const results = document.getElementById('shareSearchResults');
            if (!results) return;
            const q = (query || '').toLowerCase().trim();

            if (!q) {
                results.innerHTML = '';
                return;
            }

            // Only show users who are NOT already members
            const filtered = allShareableUsers.filter(u =>
                !allBoardSharedIds.has(u.id) &&
                (u.name.toLowerCase().includes(q) || (u.email||'').toLowerCase().includes(q))
            );

            if (!filtered.length) {
                results.innerHTML = '<div style="padding:12px;color:#9fadbc;font-size:13px;text-align:center;">No users found</div>';
                return;
            }

            results.innerHTML = filtered.map(u => renderShareRow(u, false)).join('');
        }



        function toggleBoardAccess(userId, userName, isMember) {

            if (isMember) {

                fetch(`${BASE_URL}/boards/{{ $board->id }}/unshare/` + userId, {

                    method: 'DELETE',

                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }

                }).then(r => r.json()).then(data => {

                    if (data.success) {

                        allBoardSharedIds.delete(userId);

                        filterDropdownMembers(document.getElementById('memberSearchInput')?.value || '');

                        showToast(userName + ' removed', 'success');

                        loadShareableUsers();
                        loadBoardMembers();

                    }

                });

            } else {

                fetch('\/boards/{{ $board->id }}/share', {

                    method: 'POST',

                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },

                    body: JSON.stringify({ user_id: userId })

                }).then(r => r.json()).then(data => {

                    if (data.success) {

                        allBoardSharedIds.add(userId);

                        filterDropdownMembers(document.getElementById('memberSearchInput')?.value || '');

                        showToast(userName + ' added', 'success');

                        loadShareableUsers();
                        loadBoardMembers();

                    }

                });

            }

        }



        function loadBoardMembersAndActiveUsers() { loadShareableUsers(); }


        function shareBoard(userId) {
            fetch(`${BASE_URL}/boards/{{ $board->id }}/share`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ user_id: userId })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    loadActiveUsers();
                    loadBoardMembersAndActiveUsers(); // Update dropdown
                    document.getElementById('shareEmailInput').value = '';
                    
                    // Close members dropdown if open
                    const dropdown = document.getElementById('membersDropdown');
                    if (dropdown && dropdown.style.display === 'block') {
                        dropdown.style.display = 'none';
                    }
                    
                    showToast('Board shared successfully!', 'success');
                } else {
                    showToast(data.error || 'Failed to share board', 'error');
                }
            });
        }

        function unshareBoard(userId) {
            Swal.fire({
                title: 'Remove access?',
                text: "This user will no longer be able to see or edit this board.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#eb5a46',
                cancelButtonColor: '#38414a',
                confirmButtonText: 'Yes, remove access',
                cancelButtonText: 'Cancel',
                background: '#22272b',
                color: '#b6c2cf',
                customClass: {
                    popup: 'swal2-dark',
                    confirmButton: 'swal2-confirm-dark',
                    cancelButton: 'swal2-cancel-dark'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`${BASE_URL}/boards/{{ $board->id }}/unshare/${userId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadSharedUsers();
                            loadActiveUsers();
                            loadBoardMembersAndActiveUsers(); // Update dropdown
                            showToast('Access removed successfully', 'success');
                        } else {
                            showToast(data.error || 'Failed to remove access', 'error');
                        }
                    })
                    .catch(error => {
                        showToast('Failed to remove access', 'error');
                    });
                }
            });
        }

        // Close modal when clicking outside
        document.getElementById('shareModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeShareModal();
            }
        });

        // ===== ARCHIVED SIDEBAR FUNCTIONS =====
        
        function openArchivedSidebar() {
            
            const sidebar = document.getElementById('archivedSidebar');
            const overlay = document.getElementById('archivedSidebarOverlay');
            
            
            sidebar.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            fetchArchivedCards();
        }
        
        function closeArchivedSidebar() {
            const sidebar = document.getElementById('archivedSidebar');
            const overlay = document.getElementById('archivedSidebarOverlay');
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function openActivitySidebar() {
            document.getElementById('activitySidebar').style.right = '0';
            document.getElementById('activitySidebarOverlay').style.display = 'block';
            document.body.style.overflow = 'hidden';
            document.getElementById('boardMenuDropdown').style.display = 'none';
            fetchBoardActivity();
        }

        function closeActivitySidebar() {
            document.getElementById('activitySidebar').style.right = '-420px';
            document.getElementById('activitySidebarOverlay').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        async function fetchBoardActivity() {
            const body = document.getElementById('activitySidebarBody');
            body.innerHTML = '<div style="text-align:center;padding:32px;color:#9fadbc;">Loading...</div>';
            try {
                const res = await fetch(`${BASE_URL}/boards/${boardId}/activities`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                const data = await res.json();
                if (!data.activities || !data.activities.length) {
                    body.innerHTML = '<div style="text-align:center;padding:32px;color:#9fadbc;">No activity yet.</div>';
                    return;
                }
                body.innerHTML = data.activities.map(a => `
                    <div style="display:flex;gap:12px;margin-bottom:16px;align-items:flex-start;">
                        <div style="width:32px;height:32px;border-radius:50%;background:#0052cc;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;flex-shrink:0;">${a.initials}</div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13px;color:#b6c2cf;"><strong>${a.user_name}</strong> ${a.message}</div>
                            <div style="font-size:11px;color:#9fadbc;margin-top:2px;">${a.diff}</div>
                        </div>
                    </div>
                `).join('');
            } catch(e) {
                body.innerHTML = '<div style="text-align:center;padding:32px;color:#eb5a46;">Failed to load activity.</div>';
            }
        }
        
        async function fetchArchivedCards() {
            const sidebarBody = document.getElementById('archivedSidebarBody');
            sidebarBody.innerHTML = '<div style="text-align: center; padding: 32px; color: #9fadbc;">Loading...</div>';
            
            try {
                
                const response = await fetch(`${BASE_URL}/boards/${boardId}/archived-cards`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                
                const data = await response.json();
                
                if (data.success) {
                    console.log('Success! Found', data.cards.length, 'archived cards');
                    
                    if (data.cards.length > 0) {
                        let html = '';
                        data.cards.forEach(card => {
                            html += renderArchivedCard(card);
                        });
                        sidebarBody.innerHTML = html;
                    } else {
                        sidebarBody.innerHTML = '<div class="no-archived-cards">No archived cards</div>';
                    }
                } else {
                    // console.error('API returned success=false:', data);
                    throw new Error(data.error || 'Failed to load archived cards');
                }
            } catch (error) {
        
                sidebarBody.innerHTML = `
                    <div style="padding: 20px; color: #e74c3c;">
                        <p style="font-weight: 600; margin-bottom: 8px;">Error loading archived cards</p>
                        <p style="font-size: 12px; color: #9fadbc;">${error.message}</p>
                        <p style="font-size: 12px; color: #9fadbc; margin-top: 8px;">Check browser console for details</p>
                    </div>
                `;
            }
        }
        
        function renderArchivedCard(card) {
            const title = card.title.replace(/</g, '&lt;').replace(/>/g, '&gt;');
            const listName = card.list_name.replace(/</g, '&lt;').replace(/>/g, '&gt;');
            
            return `
                <div class="archived-card-item" data-card-id="${card.id}" data-list-id="${card.list_id}">
                    <div class="archived-card-content">
                        <div class="archived-card-title">${title}</div>
                        <div class="archived-card-meta">
                            in list <strong>${listName}</strong>
                            <span class="archived-card-time">${card.archived_at}</span>
                        </div>
                    </div>
                    <div class="archived-card-actions">
                        <button class="btn-restore" onclick="restoreCard(${card.id}, ${card.list_id})">
                            Send to board
                        </button>
                        <button class="btn-delete-archived" onclick="deleteArchivedCard(${card.id}, ${card.list_id})">
                            Delete
                        </button>
                    </div>
                </div>
            `;
        }
        
        async function restoreCard(cardId, listId) {
            try {
                const response = await fetch(`${BASE_URL}/boards/${boardId}/lists/${listId}/cards/${cardId}/restore`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Remove card from sidebar
                    const cardElement = document.querySelector(`[data-card-id="${cardId}"]`);
                    if (cardElement) {
                        cardElement.remove();
                    }
                    
                    // Check if sidebar is now empty
                    const remainingCards = document.querySelectorAll('.archived-card-item');
                    if (remainingCards.length === 0) {
                        document.getElementById('archivedSidebarBody').innerHTML = '<div class="no-archived-cards">No archived cards</div>';
                    }
                    
                    showToast('Card restored successfully', 'success');
                    
                    // Refresh board to show restored card
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    throw new Error(data.error || 'Failed to restore card');
                }
            } catch (error) {
                console.error('Error restoring card:', error);
                showToast('Error restoring card', 'error');
            }
        }
        
        async function deleteArchivedCard(cardId, listId) {
            const confirmed = await Swal.fire({
                title: 'Delete card?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                background: '#22272b',
                color: '#b6c2cf'
            });
            
            if (!confirmed.isConfirmed) {
                return;
            }
            
            try {
                const response = await fetch(`${BASE_URL}/boards/${boardId}/lists/${listId}/cards/${cardId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Remove card from sidebar
                    const cardElement = document.querySelector(`[data-card-id="${cardId}"]`);
                    if (cardElement) {
                        cardElement.remove();
                    }
                    
                    // Check if sidebar is now empty
                    const remainingCards = document.querySelectorAll('.archived-card-item');
                    if (remainingCards.length === 0) {
                        document.getElementById('archivedSidebarBody').innerHTML = '<div class="no-archived-cards">No archived cards</div>';
                    }
                    
                    showToast('Card deleted permanently', 'success');
                } else {
                    throw new Error(data.error || 'Failed to delete card');
                }
            } catch (error) {
                console.error('Error deleting card:', error);
                showToast('Error deleting card', 'error');
            }
        }

        // Board Menu Functions
        function toggleBoardMenu() {
            const dropdown = document.getElementById('boardMenuDropdown');
            const isVisible = dropdown.style.display === 'block';
            dropdown.style.display = isVisible ? 'none' : 'block';
            
            if (!isVisible) {
                // Close when clicking outside
                const closeHandler = (e) => {
                    if (!dropdown.contains(e.target) && !e.target.closest('.board-icon-btn')) {
                        dropdown.style.display = 'none';
                        document.removeEventListener('click', closeHandler);
                    }
                };
                setTimeout(() => document.addEventListener('click', closeHandler), 0);
            }
        }

        let selectedBackgroundType = '{{ $board->background_type }}';
        let selectedBackgroundValue = '{{ $board->background_value }}';

        function openEditBoardModal() {
            document.getElementById('editBoardModal').style.display = 'flex';
            document.getElementById('boardMenuDropdown').style.display = 'none';
            
            // Set current values
            document.getElementById('editBoardName').value = '{{ $board->name }}';
            selectedBackgroundType = '{{ $board->background_type }}';
            selectedBackgroundValue = '{{ $board->background_value }}';
            
            // Show appropriate background options
            if (selectedBackgroundType === 'image') {
                selectBackgroundType('image');
            } else {
                selectBackgroundType('color');
            }
        }

        function closeEditBoardModal() {
            document.getElementById('editBoardModal').style.display = 'none';
        }

        function selectBackgroundType(type) {
            selectedBackgroundType = type;
            
            const colorOptions = document.getElementById('colorOptions');
            const imageOptions = document.getElementById('imageOptions');
            const colorBtn = document.getElementById('colorTypeBtn');
            const imageBtn = document.getElementById('imageTypeBtn');
            
            if (type === 'color') {
                colorOptions.style.display = 'grid';
                imageOptions.style.display = 'none';
                colorBtn.style.background = '#0079bf';
                colorBtn.style.color = 'white';
                imageBtn.style.background = '#2c333a';
                imageBtn.style.color = '#b6c2cf';
            } else {
                colorOptions.style.display = 'none';
                imageOptions.style.display = 'block';
                imageBtn.style.background = '#0079bf';
                imageBtn.style.color = 'white';
                colorBtn.style.background = '#2c333a';
                colorBtn.style.color = '#b6c2cf';
            }
        }

        function selectBoardColor(color) {
            selectedBackgroundValue = color;
            selectedBackgroundType = 'color';
            
            console.log('Selected color:', color); // Debug
            
            // Remove selection from all colors
            document.querySelectorAll('#colorOptions > div').forEach(div => {
                div.style.borderColor = 'transparent';
                div.style.boxShadow = 'none';
                div.style.transform = 'scale(1)';
                div.style.opacity = '0.7';
            });
            
            // Highlight selected color with scale and opacity
            document.querySelectorAll('#colorOptions > div').forEach(div => {
                const bgStyle = div.style.background;
                if (bgStyle && bgStyle.includes(color)) {
                    div.style.transform = 'scale(1.1)';
                    div.style.opacity = '1';
                    div.style.boxShadow = '0 4px 12px rgba(0,0,0,0.3)';
                }
            });
        }

        function selectBoardImage(imageUrl) {
            if (!imageUrl || !imageUrl.trim()) return;
            
            selectedBackgroundValue = imageUrl.trim();
            selectedBackgroundType = 'image';
            
            console.log('Selected image:', imageUrl); // Debug
            
            // Remove selection from all images
            document.querySelectorAll('#imageOptions > div:first-child > div').forEach(div => {
                div.style.borderColor = 'transparent';
                div.style.boxShadow = 'none';
                div.style.transform = 'scale(1)';
                div.style.opacity = '0.8';
            });
            
            // Highlight selected image with scale and opacity
            document.querySelectorAll('#imageOptions > div:first-child > div').forEach(div => {
                const bgImage = div.style.backgroundImage;
                if (bgImage && (bgImage.includes(imageUrl) || imageUrl.includes('unsplash'))) {
                    div.style.transform = 'scale(1.05)';
                    div.style.opacity = '1';
                    div.style.boxShadow = '0 6px 16px rgba(0,0,0,0.4)';
                }
            });
        }

        async function handleBoardImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            // Validate file type
            if (!file.type.startsWith('image/')) {
                showToast('Please select an image file', 'error');
                return;
            }
            
            // Validate file size (max 2MB for base64)
            if (file.size > 2 * 1024 * 1024) {
                showToast('Image size must be less than 2MB', 'error');
                return;
            }
            
            // Convert to base64
            const reader = new FileReader();
            reader.onload = function(e) {
                selectedBackgroundValue = e.target.result;
                selectedBackgroundType = 'image';
                
                // Show preview
                const preview = document.getElementById('uploadedImagePreview');
                preview.style.display = 'block';
                preview.querySelector('div').style.backgroundImage = `url('${e.target.result}')`;
                
                // Remove selection from other images
                document.querySelectorAll('#imageOptions > div:first-child > div').forEach(div => {
                    div.style.borderColor = 'transparent';
                    div.style.boxShadow = 'none';
                });
                
                showToast('Image loaded successfully', 'success');
            };
            reader.onerror = function() {
                showToast('Failed to load image', 'error');
            };
            reader.readAsDataURL(file);
        }

        async function saveBoardEditChanges() {
            const name = document.getElementById('editBoardName').value.trim();
            
            if (!name) {
                showToast('Board name is required', 'error');
                return;
            }
            
            if (!selectedBackgroundValue) {
                showToast('Please select a background', 'error');
                return;
            }
            
            try {
                const response = await fetch(`${BASE_URL}/boards/{{ $board->id }}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: name,
                        background_type: selectedBackgroundType,
                        background_value: selectedBackgroundValue
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Board updated successfully', 'success');
                    closeEditBoardModal();
                    // Reload page to show changes
                    setTimeout(() => location.reload(), 500);
                } else {
                    showToast(data.message || 'Failed to update board', 'error');
                }
            } catch (error) {
                console.error('Error updating board:', error);
                showToast('Failed to update board', 'error');
            }
        }
    </script>

    <!-- Archived Sidebar -->
    <div id="archivedSidebar" class="archived-sidebar">
        <div class="archived-sidebar-header">
            <h3>Archived items</h3>
            <button class="archived-sidebar-close" onclick="closeArchivedSidebar()">�</button>
        </div>
        <div id="archivedSidebarBody" class="archived-sidebar-body">
            <!-- Cards will be loaded here -->
        </div>
    </div>
    <div id="archivedSidebarOverlay" class="archived-sidebar-overlay" onclick="closeArchivedSidebar()"></div>

    <!-- Activity Sidebar -->
    <div id="activitySidebar" style="position:fixed;top:0;right:-420px;width:380px;height:100vh;background:#22272b;border-left:1px solid #3c444d;z-index:9999;display:flex;flex-direction:column;transition:right 0.3s ease;box-shadow:-4px 0 16px rgba(0,0,0,0.4);">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #3c444d;">
            <div style="display:flex;align-items:center;gap:10px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" style="color:#9fadbc;"><path d="M13 3a9 9 0 0 0-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42A8.954 8.954 0 0 0 13 21a9 9 0 0 0 0-18zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg>
                <span style="font-size:16px;font-weight:600;color:#b6c2cf;">Board Activity</span>
            </div>
            <button onclick="closeActivitySidebar()" style="background:none;border:none;color:#9fadbc;font-size:22px;cursor:pointer;width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:4px;" onmouseover="this.style.background='#2c333a'" onmouseout="this.style.background='none'">�</button>
        </div>
        <div id="activitySidebarBody" style="flex:1;overflow-y:auto;padding:16px 20px;">
            <div style="text-align:center;padding:32px;color:#9fadbc;">Loading...</div>
        </div>
    </div>
    <div id="activitySidebarOverlay" onclick="closeActivitySidebar()" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.3);z-index:9998;"></div>

    <style>
    </style>
    <script>
        // ===== NOTIFICATION SYSTEM =====
        let notifOpen = false;
        let lastNotifIds = new Set();

        async function loadNotifications() {
            try {
            const res = await fetch(`${BASE_URL}/notifications`, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
            if (!res.ok) return;
            const data = await res.json();

            // Browser push for new notifications
            if (typeof showBrowserNotif === 'function' && lastNotifIds.size > 0) {
                (data.notifications || []).forEach(n => {
                    if (!n.read && !lastNotifIds.has(n.id)) {
                        showBrowserNotif('Trello', n.message,
                            n.card_id && n.list_id ? `${BASE_URL}/boards/${n.board_id}/lists/${n.list_id}/cards/${n.card_id}` : `${BASE_URL}/boards/${n.board_id}`
                        );
                    }
                });
            }
            lastNotifIds = new Set((data.notifications || []).map(n => n.id));

            const badge = document.getElementById('notifBadge');
            if (data.unread > 0) {
                badge.style.display = 'flex';
                badge.textContent = data.unread > 9 ? '9+' : data.unread;
            } else {
                badge.style.display = 'none';
            }

            const list = document.getElementById('notifList');
            if (!data.notifications.length) {
                list.innerHTML = '<div style="padding:24px;text-align:center;color:#9fadbc;font-size:13px;">No notifications</div>';
                return;
            }
            list.innerHTML = data.notifications.map(n => `
                <div onclick="goToNotif('${n.board_id}','${n.list_id}','${n.card_id}','${n.id}')"
                    style="padding:12px 16px;border-bottom:1px solid #2c333a;cursor:pointer;background:${n.read ? 'transparent' : '#1d2d3e'};"
                    onmouseover="this.style.background='#2c333a'" onmouseout="this.style.background='${n.read ? 'transparent' : '#1d2d3e'}'">
                    <div style="font-size:13px;color:#b6c2cf;line-height:1.4;">${n.message}</div>
                    <div style="font-size:11px;color:#9fadbc;margin-top:4px;">${n.board_name} � ${n.diff}</div>
                </div>
            `).join('');
            } catch(e) { /* silent */ }
        }

        function toggleNotifDropdown() {
            const dd = document.getElementById('notifDropdown');
            notifOpen = !notifOpen;
            dd.style.display = notifOpen ? 'flex' : 'none';
            if (notifOpen) {
                loadNotifications();
                setTimeout(() => document.addEventListener('click', closeNotifOutside), 0);
            }
        }

        function closeNotifOutside(e) {
            const wrap = document.getElementById('notifBellWrap');
            if (wrap && !wrap.contains(e.target)) {
                document.getElementById('notifDropdown').style.display = 'none';
                notifOpen = false;
                document.removeEventListener('click', closeNotifOutside);
            }
        }

        function markAllRead() {
            fetch(`${BASE_URL}/notifications/mark-read`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
            .then(() => loadNotifications());
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
                // Try to find card by ID only (card might have moved to different list)
                fetch(`${BASE_URL}/boards/${boardId}/cards/${cardId}`, { method: 'HEAD' })
                    .then(r => { 
                        if (r.ok) {
                            // Card exists, navigate to it (without list_id since card might have moved)
                            window.location.href = `${BASE_URL}/boards/${boardId}/cards/${cardId}`;
                        } else {
                            // Card doesn't exist (deleted)
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
                        // Error checking card, try with list_id as fallback
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

        // Poll unread count every 3 seconds
        @auth
        loadNotifications();
        setInterval(loadNotifications, 2000);
        @endauth
        const allBoards = @json($boards);
        
        // Extract all cards from current board for search
        const boardData = @json($board);
        let allCards = [];
        if (boardData && boardData.lists) {
            boardData.lists.forEach(list => {
                if (list.cards) {
                    list.cards.forEach(card => {
                        allCards.push({
                            id: card.id,
                            name: card.title,
                            list_id: list.id,
                            list_name: list.name,
                            type: 'card'
                        });
                    });
                }
            });
        }

        function filterBoards() {
            const input = document.getElementById('boardSearchInput');
            const dropdown = document.getElementById('searchDropdown');
            const query = input.value.toLowerCase().trim();

            if (!query) { dropdown.style.display = 'none'; return; }

            // Search in boards
            const filteredBoards = (allBoards && Array.isArray(allBoards)) ? allBoards.filter(b => {
                const name = (b.name || '').toLowerCase();
                const workspace = (b.workspace || '').toLowerCase();
                return name.includes(query) || workspace.includes(query);
            }) : [];

            // Search in cards
            const filteredCards = allCards.filter(c => {
                const name = (c.name || '').toLowerCase();
                const listName = (c.list_name || '').toLowerCase();
                return name.includes(query) || listName.includes(query);
            });

            const hasResults = filteredBoards.length > 0 || filteredCards.length > 0;

            if (hasResults) {
                let html = '';

                // Add board results
                if (filteredBoards.length > 0) {
                    html += '<div style="padding:8px 14px;color:#9fadbc;font-size:11px;font-weight:600;text-transform:uppercase;">Boards</div>';
                    html += filteredBoards.map(b => `
                        <a href="${BASE_URL}/boards/${b.id}" style="display:flex;align-items:center;gap:10px;padding:10px 14px;color:#b6c2cf;text-decoration:none;border-bottom:1px solid #2c333a;transition:background 0.15s;" onmouseover="this.style.background='#2c333a'" onmouseout="this.style.background='transparent'">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="flex-shrink:0;color:#9fadbc;"><path d="M4 4h7v11H4V4zm9 0h7v7h-7V4zm0 9h7v7h-7v-7z"/></svg>
                            <div style="flex:1;"><div style="font-size:13px;font-weight:500;">${b.name}</div><div style="font-size:11px;color:#9fadbc;margin-top:2px;">${b.workspace}</div></div>
                        </a>
                    `).join('');
                }

                // Add card results
                if (filteredCards.length > 0) {
                    html += '<div style="padding:8px 14px;color:#9fadbc;font-size:11px;font-weight:600;text-transform:uppercase;margin-top:4px;">Cards</div>';
                    html += filteredCards.map(c => `
                        <a href="${BASE_URL}/boards/${boardData.id}/lists/${c.list_id}/cards/${c.id}" style="display:flex;align-items:center;gap:10px;padding:10px 14px;color:#b6c2cf;text-decoration:none;border-bottom:1px solid #2c333a;transition:background 0.15s;" onmouseover="this.style.background='#2c333a'" onmouseout="this.style.background='transparent'">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="flex-shrink:0;color:#9fadbc;"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 00.948-.684l1.498-4.493a1 1 0 011.502 0l1.498 4.493a1 1 0 00.948.684H19a2 2 0 012 2v2H3V5zm0 4h18v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg>
                            <div style="flex:1;"><div style="font-size:13px;font-weight:500;">${c.name}</div><div style="font-size:11px;color:#9fadbc;margin-top:2px;">in ${c.list_name}</div></div>
                        </a>
                    `).join('');
                }

                dropdown.innerHTML = html;
            } else {
                dropdown.innerHTML = `<div style="padding:12px 14px;color:#9fadbc;font-size:13px;text-align:center;">No boards or cards found for "<strong style="color:#b6c2cf;">${query}</strong>"</div>`;
            }
            
            // Position dropdown below search input
            const inputRect = input.getBoundingClientRect();
            dropdown.style.top = (inputRect.bottom + 5) + 'px';
            dropdown.style.left = inputRect.left + 'px';
            dropdown.style.width = inputRect.width + 'px';
            dropdown.style.display = 'block';
        }

        document.addEventListener('click', e => {
            if (!e.target.closest('.search-bar')) {
                document.getElementById('searchDropdown').style.display = 'none';
            }
        });

        // Share Link Functions
        function createShareLink() {
            const boardId = {{ $board->id }};
            fetch(`${BASE_URL}/boards/${boardId}/share-link`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('shareLinkInput').value = data.share_url;
                    document.getElementById('shareLinkContainer').style.display = 'block';
                    document.getElementById('generateLinkBtn').style.display = 'none';
                    showToast('Share link created!', 'success');
                } else {
                    showToast(data.error || 'Failed to create share link', 'error');
                }
            })
            .catch(e => showToast('Error creating share link', 'error'));
        }

        function loadExistingShareLink() {
            const boardId = {{ $board->id }};
            fetch(`${BASE_URL}/boards/${boardId}/share-link`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.share_url) {
                    document.getElementById('shareLinkInput').value = data.share_url;
                    document.getElementById('shareLinkContainer').style.display = 'block';
                    document.getElementById('generateLinkBtn').style.display = 'none';
                }
            })
            .catch(e => console.log('No existing share link'));
        }

        function copyShareLink() {
            const input = document.getElementById('shareLinkInput');
            if (!input.value) {
                showToast('No share link available', 'error');
                return;
            }
            
            navigator.clipboard.writeText(input.value).then(() => {
                showToast('Link copied to clipboard!', 'success');
            }).catch(err => {
                // Fallback for older browsers
                input.select();
                document.execCommand('copy');
                showToast('Link copied to clipboard!', 'success');
            });
        }

        function deleteShareLink() {
            const boardId = {{ $board->id }};
            
            Swal.fire({
                title: 'Delete Share Link?',
                text: 'Anyone with the link will no longer be able to join',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                background: '#22272b',
                color: '#b6c2cf'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`${BASE_URL}/boards/${boardId}/share-link`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(r => r.json())
                    .then(result => {
                        if (result.success) {
                            document.getElementById('shareLinkInput').value = '';
                            document.getElementById('shareLinkContainer').style.display = 'none';
                            document.getElementById('generateLinkBtn').style.display = 'block';
                            showToast('Share link deleted!', 'success');
                        } else {
                            showToast(result.error || 'Failed to delete share link', 'error');
                        }
                    })
                    .catch(e => showToast('Error deleting share link', 'error'));
                }
            });
        }
        function loadBoardMembers() {
            const boardId = {{ $board->id }};
            const section = document.getElementById('boardMembersSection');
            const list = document.getElementById('boardMembersList');

            fetch(`${BASE_URL}/boards/${boardId}/shared-users`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(r => r.json())
            .then(data => {
                if (!data.users || data.users.length === 0) {
                    if (section) section.style.display = 'none';
                    return;
                }
                if (section) section.style.display = 'block';
                const colors = ['#0052cc', '#ae2a19', '#216e4e', '#974f0c', '#5e4db2', '#c9372c'];
                list.innerHTML = data.users.map((u, idx) => {
                    const bgColor = colors[idx % colors.length];
                    return `
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background: #2c333a; border-radius: 6px; transition: background 0.2s;" onmouseover="this.style.background='#38414a'" onmouseout="this.style.background='#2c333a'">
                            <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, ${bgColor}, ${bgColor}dd); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0;">
                                    ${u.name.charAt(0).toUpperCase()}
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 13px; font-weight: 500; color: #b6c2cf;">${u.name}</div>
                                    <div style="font-size: 11px; color: #9fadbc; margin-top: 2px;">${u.email}</div>
                                </div>
                            </div>
                            @if($canManageMembers)
                            <button onclick="removeMember(${u.id})" style="background: none; border: none; color: #9fadbc; cursor: pointer; padding: 4px 8px; border-radius: 4px; font-size: 12px;" onmouseover="this.style.color='#e74c3c'" onmouseout="this.style.color='#9fadbc'" title="Remove">?</button>
                            @endif
                        </div>
                    `;
                }).join('');
            })
            .catch(e => console.error('Error loading members:', e));
        }

        @if($canManageMembers)
        function loadPendingRequests() {
            const boardId = {{ $board->id }};
            const section = document.getElementById('pendingApprovalsSection');
            const list = document.getElementById('pendingRequestsList');

            fetch(`${BASE_URL}/boards/${boardId}/pending-requests`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(r => r.json())
            .then(data => {
                if (!data.requests || data.requests.length === 0) {
                    if (section) section.style.display = 'none';
                    return;
                }
                if (section) section.style.display = 'block';
                list.innerHTML = data.requests.map(req => `
                    <div style="padding: 10px 12px; background: #2c333a; border-radius: 6px;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: #974f0c; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">
                                ${req.user.name.charAt(0).toUpperCase()}
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-size: 13px; font-weight: 600; color: #b6c2cf; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${req.user.name}</div>
                                <div style="font-size: 11px; color: #9fadbc; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${req.user.email}</div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button onclick="approveRequest(${req.id})" style="flex: 1; padding: 6px 0; background: #0052cc; color: white; border: none; border-radius: 4px; font-size: 12px; cursor: pointer; font-weight: 600;" onmouseover="this.style.background='#0065ff'" onmouseout="this.style.background='#0052cc'">Approve</button>
                            <button onclick="rejectRequest(${req.id})" style="flex: 1; padding: 6px 0; background: transparent; color: #9fadbc; border: 1px solid #3c444d; border-radius: 4px; font-size: 12px; cursor: pointer;" onmouseover="this.style.background='#38414a'" onmouseout="this.style.background='transparent'">Reject</button>
                        </div>
                    </div>
                `).join('');
            })
            .catch(e => console.error('Error loading pending requests:', e));
        }

        function approveRequest(requestId) {
            const boardId = {{ $board->id }};
            fetch(`${BASE_URL}/boards/${boardId}/join-requests/${requestId}/approve`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('User approved!', 'success');
                    loadPendingRequests();
                    loadBoardMembers();
                } else {
                    showToast(data.message || 'Failed to approve', 'error');
                }
            });
        }

        function rejectRequest(requestId) {
            const boardId = {{ $board->id }};
            fetch(`${BASE_URL}/boards/${boardId}/join-requests/${requestId}/reject`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Request rejected', 'success');
                    loadPendingRequests();
                } else {
                    showToast(data.message || 'Failed to reject', 'error');
                }
            });
        }
        @endif

        function removeMember(userId) {
            const boardId = {{ $board->id }};
            if (!confirm('Remove this member from the board?')) return;
            
            fetch(`${BASE_URL}/boards/${boardId}/unshare/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Member removed!', 'success');
                    loadBoardMembers();
                } else {
                    showToast(data.error || 'Failed to remove member', 'error');
                }
            })
            .catch(e => showToast('Error removing member', 'error'));
        }

        /* Drag to Scroll Functionality */
        (function() {
            const board = document.querySelector('.board-content');
            const listsContainer = document.querySelector('.lists-container');
            
            if (!board || !listsContainer) return;

            let isScrolling = false;
            let startX = 0;
            let startScrollLeft = 0;

            board.addEventListener('mousedown', (e) => {
                // Skip if clicking on a card
                if (e.target.closest('.card')) {
                    return;
                }

                // Skip if clicking on interactive elements
                if (e.target.closest('.btn, button, input, textarea, .list-menu, .list-header, .list-title, .card-menu-btn, .popover, .modal, .list-cards')) {
                    return;
                }

                isScrolling = true;
                startX = e.clientX;
                startScrollLeft = board.scrollLeft;
                board.style.cursor = 'grabbing';
                e.preventDefault();
            });

            document.addEventListener('mousemove', (e) => {
                if (!isScrolling) return;
                
                const moveX = e.clientX - startX;
                board.scrollLeft = startScrollLeft - moveX;
            });

            document.addEventListener('mouseup', () => {
                isScrolling = false;
                board.style.cursor = 'auto';
            });

            // Mouse wheel for horizontal scroll
            board.addEventListener('wheel', (e) => {
                if (e.target.closest('.list-cards')) {
                    return;
                }
                e.preventDefault();
                board.scrollLeft += e.deltaY * 0.5;
            }, { passive: false });
        })();
    </script>
</body>
</html>
