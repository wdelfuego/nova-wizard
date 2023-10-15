<template>
  <div class="nova-wizard">
    <Head title="Nova Wizard" />

    <Card
      class="flex flex-col items-left"
      style="min-height: 660px;padding:0;"
    >

    <div id="progress-container" class="bg-gray-200 dark:bg-gray-700">
      <div id="progress-bar"></div>
    </div>

    <form id="wizardForm">
      <div class="step-container">
          <template v-for="(step, index) in steps">
      
            <div class="step-wrapper">
              <Heading class="mb-6">{{ step.title }}</Heading>
      
              <component
                class="step-field"
                v-for="field in step.fields"
                :key="field.component + '-' + index"
                ref="wizardComponents"
                :is="'form-' + field.component"
                :data-attribute="field.attribute"
                :errors="errors"
                showErrors="true"
                :field="field"
                :show-help-text="true"
              />
            </div>
      
          </template>

      </div>
    </form>
          
    </Card>
    
    <div class="step-buttons">
      <DefaultButton v-if="currentStep == steps.length - 1" class="button" align="center" @click="submitButton()">
        {{ __('Submit') }}
      </DefaultButton>
      
      <OutlineButton v-if="currentStep < steps.length - 1" class="button" align="center" @click="nextButton()">
        {{ __('Next') }}
        <Icon class="icon" type="arrow-right" />
      </OutlineButton>
      
      <ToolbarButton v-if="currentStep > 0" class="button" align="center" @click="previousButton()">
        <Icon class="icon" type="arrow-left" />
        {{ __('Previous') }}
      </ToolbarButton>
    </div>
    
  </div>
</template>


<script>
import { gsap } from 'gsap';
import { ref, onMounted } from 'vue';
import { Errors } from 'form-backend-validation';

const wizardComponents = ref([]);

export default {

  setup() {
    const allWizardFields = ref([]);
    const wizardComponents = ref([]);

    onMounted(() => {
      allWizardFields.value = wizardComponents.value;
    });

    return { allWizardFields, wizardComponents };
  },
  
  mounted() {
  
    window.addEventListener('resize', () => {
      this.updateScrollPosition(0);
    });
  
    this.init();
  },

  methods: {

    init() {
      // if(this.hasStoredSettings()) {
      //   this.restoreSettings();
      //   this.reload(false);
      // }
      // else
      // {
            this.reload();
      // }
    },
    
    reload() {
      this.loading = true;
      
      // Work out the apiPath from the current Tool path, this works
      // because the ToolServiceProvider enforces that both use the same configurable uri part
      let apiUrl = '/nova-vendor/wdelfuego/nova-wizard' + this.instanceUrl();
      Nova.request().get(apiUrl)
        .then(response => { this.reloadFromResponse(response); });
    },
    
    reloadFromResponse(response)
    {
        let vue = this;
        vue.styles = response.data.styles;
        vue.windowTitle = response.data.windowTitle;
        vue.title = response.data.title;
        vue.steps = response.data.steps;
        vue.loading = false;
        // this.storeSettings();
    },
    
    instanceUrl() {
      const url = window.location.pathname.substring(Nova.url('').length);
      return url.startsWith('/') ? url : '/' + url;
    },
    
    nextButton() {
      if(document.getElementById('wizardForm').reportValidity())
      {
        this.currentStep += 1;
        this.updateScrollPosition(0.6);
      }
    },
    
    errorsForField(field) {
      console.log(this.fieldErrors[field.attribute] || {});
      return this.fieldErrors[field.attribute] || {};
    },
    
    previousButton() {
      this.currentStep -= 1;
      this.updateScrollPosition(0.8);
    },
    
    currentStepData() {
      return this.steps[this.currentStep];
    },
    
    submitButton() {
      const wizardForm = document.getElementById('wizardForm');

      if(wizardForm.reportValidity()) {
        if (this.allWizardFields.length > 0) {
          const formData = new FormData();
          this.allWizardFields.forEach((fieldComponent) => {
            if (fieldComponent.fill) {
              fieldComponent.fill(formData);
            } else {
              console.warn('fieldComponent has no fill');
            }
          });
          
          let apiUrl = '/nova-vendor/wdelfuego/nova-wizard' + this.instanceUrl();
          Nova.request().post(apiUrl, formData)
            .then(response => { 
              if(response.status === 200) {
                this.errors = new Errors();
                this.reloadFromResponse(response); 
              }
            })
            .catch(error => {
              if (error.response) {
                if(error.response.status === 500) {
                  // Handle error with status code
                  console.log('Error status:', error.response.status);
                  console.log('Error data:', error.response.data);
                }
                else if(error.response.status === 422) {
                  this.errors = new Errors(error.response.data.errors);
                  this.jumpToFirstStepWithError();
                }
                // console.log('Error status:', error.response.status);
                // console.log('Error data:', error.response.data);
              }
            });
            
        } else {
          console.warn('no wizard fields found in form');
        }
      } else {
        console.warn('wizardForm reports validity false');
      }
    },
    
    focusOnFirstFieldInStep() { 
        let attribute = this.steps[this.currentStep].fields[0].attribute;
        if(this.errors.any()) {   
            let found = false;
            this.steps[this.currentStep].fields.forEach((field) => {
                if(!found && this.errors.has(field.attribute)) {   
                    attribute = field.attribute;
                    found = true;
                }
            });
        }
        
        const divElement = document.querySelector('div[data-attribute="' + attribute + '"]');
        if(divElement) {
            const firstInput = divElement.querySelector('input');
            if (firstInput) {
              firstInput.focus();
            }
        }
    },
    
    jumpToFirstStepWithError() {
        if(!this.errors.any()) {
            return;
        }
        
        let targetStep = -1;
        let stepIndex = 0;
        this.steps.forEach((step) => {
            if(targetStep == -1) {
              step.fields.forEach((field) => {
                  if(targetStep == -1 && this.errors.has(field.attribute)) {
                    targetStep = stepIndex;
                  }
              });
            }
            stepIndex++;
        });
        
        if(targetStep > -1) {
          this.currentStep = targetStep;
          this.updateScrollPosition(1);
        }
    },
    
    updateScrollPosition(animate) {
      const container = document.querySelector('.nova-wizard .step-container');

      gsap.to(container, {
        duration: animate, // Animation duration in seconds
        scrollLeft: container.clientWidth * this.currentStep,
        ease: 'power2.out' // Easing function
      });
      
      const progressBar = document.getElementById('progress-bar');
      let percentage = 0;
      if(this.steps.length > 1) {
        percentage = this.currentStep / (this.steps.length - 1) * 100;      
      }
      
      gsap.to("#progress-bar", {
        duration: animate,
        width: `${percentage}%`,
        ease: 'power2.out' // Easing function
      });
      
      setTimeout(() => {
        this.focusOnFirstFieldInStep();
      }, animate * 1000);
    },
  },
  

  data () {
      return {
          loading: true,
          currentStep: 0,
          windowTitle: '',
          allWizardFields:[],
          errors: new Errors(),
          title: '',
          steps:[],
          styles: {
            default: { color: '#fff', 'background-color': 'rgba(var(--colors-primary-500), 0.9)' }
          }
      }
  }
  
}
</script>

<style>
/* Scoped Styles */
</style>
