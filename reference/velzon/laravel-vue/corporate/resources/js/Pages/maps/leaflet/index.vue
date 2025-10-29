<script>
import Layout from "../../../Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";

import Basic from "./basic.vue";
import Boundary from "./boundary.vue";
import Icons from "./icons.vue";
import Geometry from "./geometry.vue";
import Markers from "./markers.vue";

/**
 * Leaflet-map component
 */
export default {
  components: {
    Layout,
    PageHeader,
    Boundary,
    Icons,
    Basic,
    Geometry,
    Markers
  },
  data() {
    return {
    };
  }
};
</script>

<template>
  <Layout>
    <PageHeader title="Leaflet" pageTitle="Maps" />

    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Example</BCardTitle>
            <Basic class="leaflet-map" />
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Fixed Bounds</BCardTitle>
            <Boundary class="leaflet-map" />
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>
    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Custom Icon Example</BCardTitle>
            <Icons class="leaflet-map" />
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Geometry Example</BCardTitle>
            <Geometry class="leaflet-map" />
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>
    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle class="mb-4">Draggable marker</BCardTitle>
            <Markers class="leaflet-map" />
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>
  </Layout>
</template>
