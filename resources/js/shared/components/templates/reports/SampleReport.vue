<script setup>

function print() {
    window.print()
}
</script>

<template>
    <div class="bg-white">
        <div class="mb-3 no-print">
            <button class="btn btn-primary" @click="print">Print</button>
        </div>
        <div class="container-fluid">
            <div style="width: 100px; height: 100px">
                <img alt="" class="img-fluid" src="/logo-circle.svg">
            </div>
            <div class="table-responsive">
                <table class="table table-info">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Address</th>
                        <th>Address</th>
                        <th>Address</th>
                        <th>Address</th>
                        <th>Address</th>
                        <th>Address</th>
                        <th>Address</th>
                        <th>Address</th>
                        <th>Address</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Sample</td>
                        <td>Sample</td>
                        <td>Sample</td>
                        <td>Sample</td>
                        <td>Sample</td>
                        <td>Sample</td>
                        <td>Sample</td>
                        <td>Sample</td>
                        <td>Sample</td>
                        <td>Sample</td>
                        <td>Sample</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<style scoped>
.container-fluid {
    background-color: #fff !important;
}

@media print {
    .no-print {
        display: none;
    }
}
</style>
