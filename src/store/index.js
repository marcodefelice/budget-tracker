import {createStore} from "vuex";

const store =  createStore({
    state: {
      filter_graph: {
        year: null,
        month: null
      }
    },
  })

export default store