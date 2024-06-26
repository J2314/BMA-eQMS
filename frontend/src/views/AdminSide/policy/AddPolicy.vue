<template>
  <ion-content>
    <div class="content-wrapper">
      <div class="row">
        <div class="col-md-6">
          <div class="add-form">
            <form @submit.prevent="submitForm">
              <h1 class="form-title">Add Policy Documents</h1>
              <div class="form-group">
                <label for="documentType" class="form-label">Document Type:</label>
                <select id="documentType" class="form-control" v-model="document_type">
                  <option value="" disabled selected>Select Document Type</option>
                  <option value="Quality Policy">Quality Policy</option>
                  <option value="Environmental Policy">Environmental Policy</option>
                  <option value="Health and Safety Policy">Health and Safety Policy</option>
                </select>
              </div>
              <div class="form-group">
                <label for="documentName" class="form-label">Document Name:</label>
                <input type="text" id="documentName" class="form-control" v-model="document_name"
                  placeholder="Enter document name">
              </div>
              <!-- New Department field -->
              <div class="form-group">
                <label for="departmentId" class="form-label">Departments:</label>
                <select id="departmentId" class="form-control" v-model="department_id">
                  <option value="">Select Department</option>
                  <option v-for="(department, index) in departments" :key="index" :value="department.id">{{ department.name }}</option>
                </select>
              </div>
              <div class="form-group">
                <label for="file" class="form-label">Choose File:</label>
                <input class="form-control form-control-lg me-3" id="formFileLg" type="file" @change="fileSelected"
                  ref="file">
              </div>
              <div class="d-flex">
                <button type="submit" class="btn btn-primary btn-lg">Upload</button>
              </div>
            </form>
          </div>
          <div id="cusTable" class="table-wrapper mt-3">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th id="documentType" scope="col">Document Type</th>
                  <th id="documentName" scope="col">Document Name</th>
                  <th id="filePath" scope="col">File Path</th>
                  <th id="actions" scope="col">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(policy, index) in policies" :key="index">
                  <td>{{ index + 1 }}</td>
                  <td>{{ policy.document_type }}</td>
                  <td>{{ policy.document_name }}</td>
                  <td>{{ policy.file_path }}</td>
                  <td><button id="btnView" type="button" class="btn btn-secondary" @click="openPdf(policy.id)">View</button></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="col-md-6">
          <div class="pdf-viewer-container">
            <iframe id="pdfViewer" class="pdf-viewer" ref="pdfViewer" height="100%"></iframe>
          </div>
        </div>
      </div>
    </div>
  </ion-content>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AddPolicyPage',
  data() {
    return {
      document_type: '',
      document_name: '',
      department_id: '', // Add department_id property
      departments: [], // Add departments property
      policies: [],
    };
  },
  methods: {
    openPdf(polId) {
      axios.get(`http://127.0.0.1:8000/api/retrieve-policies/${polId}`)
        .then(response => {
          const fileContent = response.data.policy.file_path;
          const pdfViewer = this.$refs.pdfViewer;

          pdfViewer.src = fileContent;
        })
        .catch(error => {
          console.error('Error fetching file content:', error);
        });
    },
    submitForm() {
      if (!this.document_type || !this.document_name || !this.$refs.file.files[0]) {
        alert('Please fill out all fields and select a file.');
        return;
      }

      let formData = new FormData();
      formData.append('file', this.$refs.file.files[0]);
      formData.append('document_type', this.document_type);
      formData.append('document_name', this.document_name);
      formData.append('department_id', this.department_id); // Add department_id to form data

      axios.post('http://127.0.0.1:8000/api/upload-policy', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
        .then(response => {
          if (response.status === 200) {
            alert('File uploaded successfully.');
            this.document_type = '';
            this.document_name = '';
            this.department_id = ''; // Reset department_id
            this.$refs.file.value = null;
            this.fetchPolicies();
          } else {
            alert('Error uploading file.');
          }
        })
        .catch(error => {
          console.error('Error uploading file:', error);
          alert('Error uploading file.');
        });
    },
    fetchPolicies() {
      axios.get('http://127.0.0.1:8000/api/retrieve-policies')
        .then(response => {
          this.policies = response.data;
        })
        .catch(error => {
          console.error('Error fetching policies:', error);
        });
    },
    fileSelected(event) {
      const files = event.target.files;
      if (files.length > 0) {
        this.form.file = files[0];
      }
    },
    fetchDepartments() {
      axios.get('http://127.0.0.1:8000/api/departments')
        .then(response => {
          this.departments = response.data;
        })
        .catch(error => {
          console.error('Error fetching departments:', error);
        });
    },
  },
  mounted() {
    this.fetchPolicies();
    this.fetchDepartments(); // Fetch departments on component mount
  }
}
</script>

<style scoped>
.content-wrapper {
  padding: 20px;
  margin-top: 60px;
  overflow-y: auto;
}

.add-form {
  max-width: 700px;
  margin-left: 15%; 
}

.form-label {
  font-size: 18px;
  color: #333;
}

.form-control {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 16px;
  margin-bottom: 10px;
  max-height: 200px;
  overflow-y: auto;
}

.btn-primary {
  margin-top: 22px;
  background-color: #007bff;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 12px 20px;
  cursor: pointer;
  transition: background-color 0.3s;
  font-size: 16px;
  margin-bottom: 12px;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.table-wrapper {
  max-width: 700px;
  margin-left: 15%; 
  margin-right: auto;
}

.table-hover {
  width: 100%;
  margin-top: 0;
  border-collapse: collapse;
}

.table-hover th,
.table-hover td {
  border: 1px solid #ccc;
  padding: 10px;
  text-align: center;
  vertical-align: middle;
}

.table-hover th {
  background-color: #f0f0f0;
}

.form-control-lg {
  width: 100%;
  flex: 1;
}

.align-items-center {
  width: 100%;
}

.pdf-viewer-container {
  margin-top: 20px;
  margin-left: auto;
  margin-right: auto;
  width: 800px;
  height: 700px;
  border: 1px solid #ccc;
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
}

.pdf-viewer {
  width: 100%;
  height: 100%;
}
</style>
