<script setup>

import BaseContainer from "@/js/shared/components/base/BaseContainer.vue";
import {SearchIcon} from "vue-tabler-icons";
import {onMounted} from "vue";
import {useAuthStore} from "@/js/shared/stores/AuthStore";

const auth = useAuthStore()

onMounted(() => {
    console.log(auth.getUser())
})
</script>
<template>
    <base-container>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <div class="input-icon">
                                <input class="form-control form-control-lg" placeholder="Search employee…" type="text"/>
                                <span class="input-icon-addon">
                                    <search-icon class="icon"/>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </base-container>
    <base-container>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th class="text-nowrap">Heading 1</th>
                                    <th class="text-nowrap">Heading 2</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <th>1</th>
                                    <td>Cell</td>
                                </tr>
                                <tr>
                                    <th>2</th>
                                    <td>Cell</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </base-container>
</template>
