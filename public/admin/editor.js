import React, {Component} from "react";
import "../components.css";
import { connect } from "react-redux";
import moment from "moment";

import axios from "axios";

const mapStateToProps = state => {

    return { editorPosts: state.editorPosts };

};

class Image2 extends Component {

	constructor(props) {

		super(props);
		this.state = {

			removing: false,
			visible: false
	

		}

		this.removePicture = this.removePicture.bind(this);

		this.showImage = this.showImage.bind(this);

	
	}

	showImage() {

		this.setState({ visible: true });

	}
	

	removePicture(e) {

			let len;

			this.props.editorPosts.posts.map((post) => {

				if (post.id === this.props.galleryId) {

					len = post.items.length;
					return post;

				}

				return post;

			})

			if (len === 1) {

				if (window.confirm("Are you sure?")) {

					let body = {};
					body.id = this.props.galleryId;
					body.type = "removeGallery";
					body.directory = this.props.item.directory;
					body.fileName = this.props.item.fileName;
					e.target.removeEventListener("click", this.removeGallery);
					axios.post("/gallery/edit", body).then((res) => {

						this.props.dispatch({ type: "REMOVE_GALLERY", id: this.props.galleryId });
		
					}).catch((e) => {console.log(e)});

			

				} else {


				}

			} else {

				let body = {};
				body.id = this.props.item.id;
				body.type = "removePicture";
				body.fileName = this.props.item.fileName;
				body.directory = this.props.item.directory;
				e.target.removeEventListener("click", this.removePicture);

				axios.post("/gallery/edit", body).then((res) => {

					this.props.dispatch({ type: "REMOVE_IMAGE", id: this.props.item.id, galleryId: this.props.galleryId });
		
				}).catch((e) => {console.log(e)});

			}

	}

	render() { 

		let onClick = this.removePicture;
		let opacity = 0;

		if (this.state.removing) {

			onClick="";

		}

		if (this.state.visible) {

			opacity = 1;

		}

		return (<React.Fragment><div 
		className="editor-image" onClick={onClick} 
		title="Click to remove" 
		style={{opacity: opacity, backgroundImage:`url("${this.props.item.directory}/thumbnail/${this.props.item.fileName}")`}}>
		</div><img onLoad={this.showImage} 
		alt="galleryimage"
		style={{display: "none"}} 
		src={`${this.props.item.directory}/thumbnail/${this.props.item.fileName}`} />
		</React.Fragment>);


	}

}

const Image = connect(mapStateToProps)(Image2);

class EditGallery2 extends Component {

	constructor(props) {

		super(props);
		this.state = {

			title: "",
			description: "",
			editTitle: false,
			editDescription: false,
			titleError: false,
			descriptionError: false,
			piece: 10
	

		}

		this.editTitle = this.editTitle.bind(this);
		this.editDescription = this.editDescription.bind(this);

		this.input = React.createRef();
		this.textarea = React.createRef();

		this.changeTitle = this.changeTitle.bind(this);
		this.changeDescription = this.changeDescription.bind(this);
		this.changeTitleEnter = this.changeTitleEnter.bind(this);

		this.removeGallery = this.removeGallery.bind(this);

		this.ajaxEditTitle = this.ajaxEditTitle.bind(this);
		this.ajaxEditDescription = this.ajaxEditDescription.bind(this);

		this.more = this.more.bind(this);

		this.addOneImage = this.addOneImage.bind(this);
		this.fileInput = React.createRef();

		
	}

	addOneImage(e) {

		e.preventDefault();

		let formData = new FormData();

		if (this.fileInput.current.files.length === 1) {
			
			let fileName;

			for (let i = 0; i < this.fileInput.current.files.length; i++) {

				formData.append('files[]', this.fileInput.current.files[i], this.fileInput.current.files[i].name);
				fileName = this.fileInput.current.files[i].name;

			}

			formData.append('gallery', this.props.gallery.id);
			formData.append('directory', this.props.gallery.items[0].directory);

			axios({
				
				method: "post", 
				url:"/gallery/post", 
				data: formData, 
				headers: {contentType: false, processData: false}
			})
			.then((res) => {

				if (res.data.uploaded) {

					let image = {
						
						id: res.data.uploaded,
						directory: this.props.gallery.items[0].directory,
						gallery: this.props.gallery.id,
						fileName: fileName	

					   };

					this.props.dispatch({ type: "ADD_IMAGE", item: image, id: this.props.gallery.id});

				}

			}).catch((e) => {console.log(e)});

		}
		


	}

	more() {


		this.setState({ piece: this.state.piece + 10 });

	}

	ajaxEditTitle(value) {

				let body = {};
				body.id = this.props.gallery.id;
				body.type = "editTitle";
				body.value = value;

				axios.post("/gallery/edit", body).then((res) => {

					if (res.data.titleError) {

						this.setState({ titleError: true });

					}

					else {

						this.props.dispatch({ type: "EDIT_TITLE", id: this.props.gallery.id, title: value });
						this.setState({ title: value, editTitle: !this.state.editTitle, titleError: false });

					}

		
				}).catch((e) => {console.log(e)});

	}

	ajaxEditDescription(value) {

					let body = {};
					body.id = this.props.gallery.id;
					body.type = "editDescription";
					body.value = value;

					axios.post("/gallery/edit", body).then((res) => {

						if (res.data.titleError) {

							this.setState({ titleError: true });

						}

						else {

							this.props.dispatch({ type: "EDIT_DESCRIPTION", id: this.props.gallery.id, description: value });
							this.setState({ description: value, editDescription: !this.state.editDescription, descriptionError: false });

						}

						
		
					}).catch((e) => {console.log(e)});


	}

	removeGallery(e) {

		if (window.confirm("Are you sure?")) {

			let body = {};
			body.id = this.props.gallery.id;
			body.directory = this.props.gallery.items[0].directory;
			body.type = "removeGallery";

			e.target.removeEventListener("click", this.removeGallery);

			axios.post("/gallery/edit", body).then((res) => {
				
				if (res.data.deleted) {

					this.props.dispatch({ type: "REMOVE_GALLERY", id: this.props.gallery.id });

				}
		
			}).catch((e) => {console.log(e)});

			

		} else {

			


		}
	

	}

	changeTitle(e) {

		let value = e.target.value.trim();

		if (value === this.state.title) {

			this.setState({ editTitle: !this.state.editTitle });

		} else {

			if (value === "" || value.length > 100) {


			} else {

				this.ajaxEditTitle(value);
				
			}
		
		}

	}

	changeTitleEnter(e) {

		let value = e.target.value.trim();

		if (e.key === 'Enter') {

			if (value === this.state.title) {

				this.setState({ editTitle: !this.state.editTitle });

			} else {

				if (value === "" || value.length > 100) {


				} else {
			
					this.ajaxEditTitle(value);
					

				}
		
			}

		}

	}

	changeDescription(e) {

		let value = e.target.value.trim();

		if (value === this.state.description) {

			this.setState({ editDescription: !this.state.editDescription });

		}

		else {

			if (value === "" || value.length > 500) {


			}

			else {

				this.ajaxEditDescription(value);

					
			}

		}
	
	}

	editTitle() {

		this.setState({ editTitle: !this.state.editTitle });

		window.setTimeout(() => {

			this.input.current.focus();

		}, 1);

	}
	
	editDescription() {

		this.setState({ editDescription: !this.state.editDescription });
		
		window.setTimeout(() => {

			this.textarea.current.focus();

		}, 1);

	}

	componentDidMount() {


		this.setState({ title: this.props.gallery.title, description: this.props.gallery.description });

	}

	render() {

		let images;
		let titleError = {};
		let descriptionError = {};

		if (this.state.titleError) {
		
			titleError = { color: "red" };

		}

		if (this.state.descriptionError) {
		
			descriptionError = { color: "red" };

		}

		let title = <span onClick={this.editTitle} title="Click to edit">{this.props.gallery.title}</span>;
		let description = <span onClick={this.editDescription} title="Click to edit">{this.props.gallery.description}</span>;
		let slicedGallery = this.props.gallery.items.slice(0, this.state.piece);
		let more = <div className="more" onClick={this.more} >More</div>;
		let add = <div className="add-one-image">
			   <form onSubmit={this.addOneImage} className="add-one-image-form">
				<input accept="image/x-png, image/gif, image/jpeg" style={{ alignSelf: "center", width: "100%"}} type="file" name="files" ref={this.fileInput} />
				<input style={{ marginTop: "0.5rem", backgroundColor: "white", padding: "0.5rem", cursor:"pointer" }} type="submit" value="Submit" />
			   </form>
			   </div>;

		if (this.state.piece >= this.props.gallery.items.length) {

			more = "";

		}

		if (this.props.gallery.items.length > 30) {

			add = "";

		}

		if (this.state.editTitle) {

			title = <input style={titleError} onKeyUp={this.changeTitleEnter} onBlur={this.changeTitle} ref={this.input} className="title-input" type="text" defaultValue={this.props.gallery.title} />;

		}

		if (this.state.editDescription) {

			description = <textarea style={descriptionError} onBlur={this.changeDescription} ref={this.textarea} className="description-input" defaultValue={this.props.gallery.description}></textarea>;

		}

		
		if (this.props.gallery.type === "images") {

			images = slicedGallery.map((item, index) => {

				return <Image galleryId={this.props.gallery.id} key={index} item={item} />

			});

		}




		return (<div className="post">
			<div className="post-panel">
			<span style={{fontSize: "0.9rem"}}>
			{moment(new Date(this.props.gallery.date)).format('MM/DD/YYYY')}</span>
			{title}
			{description}
			<span onClick={this.removeGallery} title="Remove Gallery" style={{color: "red", cursor: "pointer"}}>Remove</span>
			</div>
			<div className="post-items">
			{images}
			{more}
			{add}
			</div>
			</div>
			);

	}


}

const EditGallery = connect(mapStateToProps)(EditGallery2);

export default EditGallery;
