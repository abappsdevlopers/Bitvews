<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>BitView Admin - تجربة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #0f172a; color: #f8fafc; font-family: 'Cairo', sans-serif; }
        .card { background-color: #1e293b; border-radius: 20px; border: none; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3); }
        .stat-card { border-left: 5px solid #38bdf8; }
        .table { color: #cbd5e1; }
        .btn-action { border-radius: 10px; }
    </style>
</head>
<body class="p-3">

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-white"><i class="fas fa-tools text-primary"></i> لوحة التحكم (تجربة)</h4>
            <button class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i></button>
        </div>

        <div class="row g-2 mb-4">
            <div class="col-6">
                <div class="card stat-card p-3 text-center">
                    <h5 class="mb-0">150</h5>
                    <small class="text-secondary">مستخدم</small>
                </div>
            </div>
            <div class="col-6">
                <div class="card p-3 text-center border-start border-warning border-4">
                    <h5 class="mb-0">5</h5>
                    <small class="text-secondary">سحب معلق</small>
                </div>
            </div>
        </div>

        <div class="card p-3">
            <h6 class="mb-3 text-warning"><i class="fab fa-paypal"></i> طلبات السحب (وهمية)</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-secondary">
                            <th>الإيميل</th>
                            <th>المبلغ</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>user1@test.com</td>
                            <td><span class="fw-bold">10$</span></td>
                            <td>
                                <button class="btn btn-success btn-sm btn-action"><i class="fas fa-check"></i></button>
                                <button class="btn btn-outline-danger btn-sm btn-action"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>demo@test.com</td>
                            <td><span class="fw-bold">25$</span></td>
                            <td>
                                <button class="btn btn-success btn-sm btn-action"><i class="fas fa-check"></i></button>
                                <button class="btn btn-outline-danger btn-sm btn-action"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
