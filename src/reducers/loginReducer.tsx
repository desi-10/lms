type IStudent = {
  index_number: string;
  password: string;
};

export interface IDefaultState {
  student: IStudent;
  loading: boolean;
  error: boolean;
  errorMessage: string;
}

export type Actions =
  | {
      type: "INDEX_NUMBER";
      payload: string;
    }
  | { type: "PASSWORD"; payload: string }
  | { type: "LOADING"; payload: "" }
  | { type: "ERROR"; payload: string }
  | { type: "NO_ERROR"; payload: string };

export const studentReducer = (state: IDefaultState, action: Actions) => {
  const { type, payload } = action;

  switch (type) {
    case "INDEX_NUMBER":
      return {
        ...state,
        student: { ...state.student, index_number: payload },
      };
    case "PASSWORD":
      return {
        ...state,
        student: { ...state.student, password: payload },
      };
    case "LOADING":
      return {
        ...state,
        loading: true,
      };
    case "ERROR":
      return {
        ...state,
        loading: false,
        error: true,
        errorMessage: payload,
      };
    case "NO_ERROR":
      return {
        ...state,
        loading: false,
        error: false,
        errorMessage: payload,
      };
    default:
      return state;
  }
};
