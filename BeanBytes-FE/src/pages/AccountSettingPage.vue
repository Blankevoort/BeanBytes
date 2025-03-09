<template>
  <q-page class="bg-dark text-secondary row justify-center">
    <div class="bg-dark text-white text-grey-6 col-xs-12 col-sm-10 col-md-10 col-lg-8 col-xl-8">
      <div class="text-bold q-py-md q-px-sm" style="font-size: 24px">Account</div>

      <q-form>
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 q-pa-sm">
            <q-input class="form-inputs" bordered placeholder="Fullname" />
          </div>

          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 q-pa-sm">
            <q-input class="form-inputs" bordered placeholder="Username" />
          </div>

          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 q-pa-sm">
            <q-input class="form-inputs" bordered placeholder="Email" />
          </div>

          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 q-pa-sm">
            <q-input class="form-inputs" bordered placeholder="Phone" />
          </div>

          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 q-pa-sm">
            <q-input class="form-inputs" bordered placeholder="Job Title" />
          </div>
        </div>

        <div class="col-12 q-px-sm q-gutter-x-md q-py-md">
          <q-btn label="submit" type="submit" />

          <q-btn label="Reset" type="reset" />
        </div>
      </q-form>
    </div>
  </q-page>
</template>

<style>
.form-inputs input {
  width: 100%;
  background-color: #2a2a2a;
  border-radius: 4px;
  padding: 0.75rem;
  color: #fff;
  font-size: 0.9rem;
  resize: vertical;
}

.form-inputs input::placeholder {
  color: #888;
}

.form-inputs input:focus {
  outline: 2px solid #555;
}
</style>
